<?

$user=$request->data['details']->user->getUsername();
$date=formatdate($request->data['details']->date);

$takeover=new Request($request);
$takeover->query['forcelock']='continue';
$discard=new Request($request);
$discard->query['forcelock']='discard';
	
?>
<div class="header">
<h2>Resource Locked</h2>
</div>
<div class="body">
<h3>This resource is locked by <?= $user ?></h3>
<h2><?= $user ?> last edited the working copy at <?= $date ?></h2>
<p>If you were previously editing this resource, they have taken over editing and
 may or may not have discarded your changes.</p>
<p>You must choose how to proceed:</p>
<p><a href="<?= $takeover->encode() ?>">Take over the lock and edit from the point <?= $user ?> left off.</a></p>
<p><a href="<?= $discard->encode() ?>">Take over the lock and discard the changes <?= $user ?> made.</a></p>
<p><a href="<?= $request->nested->encode() ?>">Cancel trying to edit.</a></p>
</div>
