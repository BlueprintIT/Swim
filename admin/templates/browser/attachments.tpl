{apiget var="item" type="item" id=$request.query.item}
{assign var="itemvariant" value=$item->getVariant($request.query.variant)}
{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{getfiles var="files" itemversion=$itemversion->getId()}
{include file="browser/filelist.tpl" scope="version" title="Item Attachments" files=$files}
