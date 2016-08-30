<?php

/**
 * SmartMySQL v1.3 - MySQL database access wrapper.
 * http://www.phpclasses.org/smartmysql
 * 
 * Realised Under The MIT License (http://www.opensource.org/licenses/mit-license.php)
 * Copyright (c) 2008 Otar Chekurishvili (www.ottobyte.com)
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class SmartMySQL {

    private $connection = 0;
    public $cache = false;
    public $cachedir = 'cache/';
    public $expire = 0;
    private $fetchmode = MYSQL_ASSOC;
    private $errors = array(
        'dbconnect' => 'Could not connect to server. Make sure that hostname is correct.',
        'dbselect' => 'Could not select database. Make sure that it exists.',
        'dbclose' => 'Could not close database connection. Make sure that it is connected.',
        'execute' => 'Could not execute operation. Requested query is not correct.',
        'retrieve' => 'Could not retrieve result. Make sure that your arguments are correct.',
        'fetch' => 'Could not return fetched results as Array. Make sure that they exist.',
        'fetchall' => 'Could not return fetched results as two-dimentional Array. Make sure that they exist.',
        'fetchmode' => 'Could not set fetch mode. It is not defined correctly.',
        'queryinsert' => 'Could not generate insert query. Make sure that data array is defined correctly.',
        'queryupdate' => 'Could not generate update query. Make sure that data array is defined correctly.',
        'escape' => 'Could not escape string. Request is not correct.',
        'cachecheck' => 'Could not set chache expire time. It is not defined correctly.',
        'cacheopen' => 'Could not open cached results. File may be corrupted or it does not exist.',
        'cachesave' => 'Could not save results in cache file, check your query.'
    );

    public function connect($server, $username, $password, $database, $persistent = null)
    {
        if (is_null($persistent)) {
            $this->connection = @mysql_connect($server, $username, $password);
        } else {
            $this->connection = @mysql_pconnect($server, $username, $password);
        }
        if ($this->connection) {
            $selectdb = @mysql_select_db($database, $this->connection);
            return true;
            if (!$selectdb) {
               // $this->error($this->errors['dbselect']);
                return false;
            }
        } else {
           // $this->error($this->errors['dbconnect']);
            return false;
        }
    }

    public function close()
    {
        $close = @mysql_close($this->connection);
        if (!$close) {
            $this->error($this->errors['dbclose']);
        }
    }

    public function execute($query)
    {
        $res = @mysql_query($query, $this->connection);
        if (!$res) {
        	return false;
            //$this->error($this->errors['execute']);
        } else {
            return $res;
        }
    }

    public function retrieve($column, $table, $field, $value)
    {
        $value = is_numeric($value) ? $value : '\'' . $value . '\'';
        $sql = "SELECT `$column` FROM `$table` WHERE `$field` = $value;";
        $res = $this->execute($sql);
        if (empty($this->cache)) {
            $ret = @mysql_result($res, 0, $column);
        } else {
            if ($this->cachizer('check', 'retrieve', $sql)) {
                $ret = $this->cachizer('open', 'retrieve', $sql);
            } else {
                $ret = @mysql_result($res, 0, $column);
                $this->cachizer('save', 'retrieve', $sql, $ret);
            }
        }
        if ($ret) {
            return $ret;
        } else {
        	return false;
         //   $this->error($this->errors['retrieve']);
        }
    }

    public function fetch($query)
    {
        if (empty($this->cache)) {
            $ret = $this->fetcher($query);
        } else {
            if ($this->cachizer('check', 'fetch', $query)) {
                $ret = $this->cachizer('open', 'fetch', $query);
            } else {
                $ret = $this->fetcher($query);
                $this->cachizer('save', 'fetch', $query, $ret);
            }
        }
        if ($ret) {
            return $ret;
        } else {
        	return false;
         //   $this->error($this->errors['fetch']);
        }
    }

    public function fetchAll($query)
    {
        if (empty($this->cache)) {
            $ret = $this->fetcher($query, true);
        } else {
            if ($this->cachizer('check', 'fetchall', $query)) {
                $ret = $this->cachizer('open', 'fetchall', $query);
            } else {
                $ret = $this->fetcher($query, true);
                $this->cachizer('save', 'fetchall', $query, $ret);
            }
        }
        if ($ret) {
            return $ret;
        } else {
        	return false;
         //   $this->error($this->errors['fetchall']);
        }
    }

    public function fetchMode($type)
    {
        $fetchmodes = array('assoc', 'num', 'both');
        if (in_array($type, $fetchmodes)) {
            switch ($type) {
                case 'assoc':
                    $fm = MYSQL_ASSOC;
                break;
                case 'num':
                    $fm = MYSQL_NUM;
                break;
                case 'both':
                    $fm = MYSQL_BOTH;
                break;
            }
            $this->fetchmode = $fm;
        } else {
        	return false;
           // $this->error($this->errors['fetchmode']);
        }
    }

    public function queryInsert($table, $data = array())
    {
        if (is_array($data)) {
			$field = null;
			$column = null;
            foreach ($data as $k => $v) {
                $field .= '`' . $k . '`, ';
                $column .= '\'' . $this->escape($v) . '\', ';
            }
            $field = substr($field, 0, -2);
            $column = substr($column, 0, -2);
            $ret = "INSERT INTO `$table` ($field) VALUES ($column);";
        } else {
            $this->error($this->errors['queryinsert']);
            $ret = false;
        }
        return $ret;
    }

    public function queryUpdate($table, $data = array(), $other = null)
    {
        if (is_array($data)) {
            $sql = null;
            foreach ($data as $k => $v) {
                $v = '\''. $this->escape($v) .'\'';
                $sql .= '`' . $k . '`' . ' = ' . $v . ', ';
            }
            $sql = substr($sql, 0, -2);
            $other = is_null($other) ? null : ' ' . $other;
            $ret = "UPDATE `$table` SET $sql$other;";
        } else {
            $this->error($this->errors['queryupdate']);
            $ret = false;
        }
        return $ret;
    }

    public function escape($string)
    {
        if (get_magic_quotes_gpc()) {
			$string = stripslashes($string);
        }
        $ret = is_numeric($string) ? $string : @mysql_real_escape_string($string, $this->connection);
     //   if (empty($ret)) {
     //       $this->error($this->errors['escape']);
     //   } else {
            return $ret;
     //   }
    }

    public function viewData($data)
    {
        echo "\n" . '<pre>' . "\n";
        echo substr(print_r($data), 0, -1);
        echo '</pre>' . "\n\n";
    }

    private function error($message)
    {
        $error = mysql_error();
        $errno = mysql_errno();
        $ret = '<pre>' . "\n";
        if (!is_null($message)) {
            $ret .= "\t" . '<b>' . $message . '</b>' . "\n";
        }
        if (!empty($errno) && !empty($error)) {
            $ret .= "\t" . '<b>' . $errno . ':</b> ' . $error . "\n";
        }
        $ret .= '</pre>' . "\n";
        echo $ret;
    }

    private function fetcher($query, $secrun = null)
    {
		$ret = null;
        $res = $this->execute($query);
        if (is_null($secrun)) {
            $ret = @mysql_fetch_array($res, $this->fetchmode);
        } else {
            while ($r = @mysql_fetch_array($res, $this->fetchmode)) {
                $ret[] = $r;
            }
        }
        return $ret;
    }

    private function cachizer($mode, $type, $query, $data = null)
    {
        $filename = $this->cachedir . md5($type . $query) . '.cache';
        switch ($mode) {
            case 'check':
                if (!is_numeric($this->expire)) {
                    $exptypes = array('m', 'h', 'd');
                    $explen = strlen($this->expire);
                    $exptype = $this->expire[$explen - 1];
                    $exp = substr($this->expire, 0, -1);
                    if (in_array($exptype, $exptypes)) {
                        switch ($exptype) {
                            case 'm':
                                $this->expire = $exp * 60;
                            break;
                            case 'h':
                                $this->expire = $exp * 3600;
                            break;
                            case 'd':
                                $this->expire = $exp * 86400;
                            break;
                        }
                    } else {
                        $this->error($this->errors['cachecheck']);
                    }
                }
                if (file_exists($filename) && filemtime($filename) > (time() - $this->expire)) {
                    $ret = true;
                } else {
                    $ret = false;
                }
                break;
            case 'open':
                $res = file_get_contents($filename);
                if ($res) {
                    $ret = unserialize($res);
                } else {
                    $this->error($this->errors['cacheopen']);
                }
                break;
            case 'save':
                $res = serialize($data);
                if ($res) {
                    $ret = file_put_contents($filename, $res);
                } else {
                    $this->error($this->errors['cachesave']);
                }
                break;
        }
        return $ret;
    }

}

?>
