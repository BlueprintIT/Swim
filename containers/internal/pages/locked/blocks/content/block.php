<?

$user=$request->data['details']->user->getUsername();
$date=formatdate($request->data['details']->date);

$takeover=$request;
$takeover->query['forcelock']='continue';
$discard=$request;
$discard->query['forcelock']='discard';
	
?>
<h1>This resource is locked by <?= $user ?></h1>
<h2><?= $user ?> last edited the working copy at <?= $date ?></h2>
<p>If you were previously editing this resource, they have taken over editing and
 may or may not have discarded your changes.</p>
<p>You must choose how to proceed:</p>
<p><a href="<?= $takeover->encode() ?>">Take over the lock and edit from the point <?= $user ?> left off.</a></p>
<p><a href="<?= $discard->encode() ?>">Take over the lock and discard the changes <?= $user ?> made.</a></p>
<p><a href="<?= $request->nested->encode() ?>">Cancel trying to edit.</a></p>
