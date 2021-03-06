{*
 * $Revision: 15342 $
 * If you want to customize this file, do not edit it directly since future upgrades
 * may overwrite it.  Instead, copy it into a new directory called "local" and edit that
 * version.  Gallery will look for that file first and use it if it exists.
 *}
<div class="gbBlock gcBackground1">
  <h2> {g->text text="Upload Complete"} </h2>
</div>

<div class="gbBlock">
  <h3>
    {if isset($ItemAddConfirmation.count)}
      {g->text one="Successfully added %d file." many="Successfully added %d files."
	       count=$ItemAddConfirmation.count arg1=$ItemAddConfirmation.count}
    {else}
      {g->text text="No files added."}
    {/if}
  </h3>

  {foreach from=$ItemAddConfirmation.status.addedFiles item=entry}
    {if $entry.exists}
    {capture name="itemLink"}
    <a href="{g->url arg1="view=core.ShowItem" arg2="itemId=`$entry.id`"}">
      {$entry.fileName}
    </a>
    {/capture}
    {g->text text="Added %s" arg1=$smarty.capture.itemLink}
    {else}
    {g->text text="Failed to add %s" arg1=$entry.fileName}
    {/if}
    <br/>
    {if !empty($entry.warnings)}
      <div class="giWarning">
      {foreach from=$entry.warnings item=warning}
	{$warning} <br/>
      {/foreach}
      </div>
    {/if}
  {/foreach}
</div>

<div class="gbBlock">
  <a href="{g->url arg1="view=core.ItemAdmin" arg2="subView=core.ItemAdd"
   arg3="itemId=`$ItemAdmin.item.id`"}">
    {g->text text="Add more files"}
  </a>
</div>
