<?
	$commit = new Request();
	$commit->method='commit';
	$commit->query['version']=$request->query['version'];
	$commit->resource=$request->resource;
	$commit->nested=$request->nested;

	$cancel = new Request();
	$cancel->method='cancel';
	$cancel->query['version']=$request->query['version'];
	$cancel->resource=$request->resource;
	$cancel->nested=$request->nested;
?>
<p><a href="<?= $commit->encode() ?>">Commit</a></p>
<p><a href="<?= $cancel->encode() ?>">Cancel</a></p>
