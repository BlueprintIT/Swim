<?

$icon = new Request();
$icon->method='view';
$icon->resource='global/template/base/layout/print.jpg';
$hover = new Request();
$hover->method='view';
$hover->resource='global/template/base/layout/printhover.jpg';

?>

imageicon = new Image();
imageicon.src='<?= $icon->encode() ?>';
imagehover = new Image();
imagehover.src='<?= $hover->encode() ?>';

function mouseOverPrint()
{
	var image = document.getElementById('printicon');
	image.src=imagehover.src;
}

function mouseOutPrint()
{
	var image = document.getElementById('printicon');
	image.src=imageicon.src;
}

function mouseClickPrint()
{
	var link = document.getElementById('printlink');
	if (link)
	{
		var win = window.open(link.getAttribute('href'));
		if (win)
		{
			return false;
		}
	}
	return true;
}
