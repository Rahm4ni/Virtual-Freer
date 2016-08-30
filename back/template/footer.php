<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
?>
		</div>
		<div id="right-column">
<?
if (check_login())	{
	$news = @file_get_contents('http://freer.ir/xml.php?'.$server[HTTP_HOST]);
	$news = xml2array($news,0);
	if ($news)
		foreach ($news[rss][channel][item] as $detail)
		{
			$body .= '<li><a href="'.$detail[link].'" target="_blank">'.$detail[title].'</li>';
		}
?>
			<strong class="h">اخبار</strong>
			<div class="box">
				<ul>
					<?=$body?>
				</ul>
			</div>
<?	}	?>
	  </div>
	</div>
	<div id="footer"></div>
</div>
</body>
</html>
