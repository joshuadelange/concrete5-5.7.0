<? defined('C5_EXECUTE') or die("Access Denied."); ?>

<? if ($this->controller->getTask() == 'edit' && is_object($pagetype)) { ?>

<form class="form-horizontal" method="post" action="<?=$view->action('submit', $pagetype->getPageTypeID())?>">
<div class="ccm-pane-body">
<?=Loader::element('page_types/form/base', array('pagetype' => $pagetype));?>
</div>
<div class="ccm-dashboard-form-actions-wrapper">
<div class="ccm-dashboard-form-actions">
	<a href="<?=$view->url('/dashboard/pages/types')?>" class="btn btn-default pull-left"><?=t('Cancel')?></a>
	<button class="pull-right btn btn-primary" type="submit"><?=t('Save')?></button>
</div>
</div>

</form>

<? } else {
	$pk = PermissionKey::getByHandle('access_page_type_permissions');
	 ?>


	<? if (count($pagetypes) > 0) { ?>

	<table class="table table-striped">
	<thead>
		<tr>
			<th><?=t('Name')?></th>
			<td class="page-type-tasks">
				<a href="<?=$view->url('/dashboard/pages/types/add')?>" class="btn btn-small btn-primary pull-right"><?=t('Add Page Type')?></a>
			</td>
		</tr>
	</thead>
	<tbody>
		<? foreach($pagetypes as $cm) {  ?>
		<tr>
			<td class="page-type-name"><?=$cm->getPageTypeName()?></td>
			<td class="page-type-tasks">
				<a href="<?=$view->action('edit', $cm->getPageTypeID())?>" class="btn btn-default btn-xs"><?=t('Basic Details')?></a>
				<a href="<?=$view->url('/dashboard/pages/types/form', $cm->getPageTypeID())?>" class="btn btn-default btn-xs"><?=t('Edit Form')?></a>
				<a href="<?=$view->url('/dashboard/pages/types/output', $cm->getPageTypeID())?>" class="btn btn-default btn-xs"><?=t('Output')?></a>
				<? if ($pk->can()) { ?>
					<a href="<?=$view->url('/dashboard/pages/types/permissions', $cm->getPageTypeID())?>" class="btn btn-default btn-xs"><?=t('Permissions')?></a>
				<? } ?>
				<a href="#" data-delete="<?=$cm->getPageTypeID()?>" class="btn btn-default btn-xs btn-danger"><?=t('Delete')?></a>

				<div style="display: none">
					<div data-delete-dialog="<?=$cm->getPageTypeID()?>">
						<form data-delete-form="<?=$cm->getPageTypeID()?>" action="<?=$view->action('delete', $cm->getPageTypeID())?>" method="post">
						<?=t("Delete this page type? This cannot be undone.")?>
						<?=Loader::helper('validation/token')->output('delete_page_type')?>
						</form>
					</div>
				</div>
			</td>
		</tr>
		<? } ?>
	</tbody>
	</table>

	<? } else { ?>
		<p><?=t('You have not created any page types yet.')?></p>
		<a href="<?=$view->url('/dashboard/pages/types/add')?>" class="btn btn-primary"><?=t('Add Page Type')?></a>
	<? } ?>

	<style type="text/css">
	td.page-type-name {
		width: 100%;
	}

	td.page-type-tasks {
		text-align: right !important;
		white-space: nowrap;
	}
	</style>

	<script type="text/javascript">
	$(function() {
		$('a[data-delete]').on('click', function() {
			var ptID = $(this).attr('data-delete');
			$('div[data-delete-dialog=' + ptID + ']').dialog({
				modal: true,
				width: 320,
				dialogClass: 'ccm-ui',
				title: '<?=t("Delete Page Type")?>',
				height: 200, 
				buttons: [
					{
						'text': '<?=t("Cancel")?>',
						'class': 'btn pull-left',
						'click': function() {
							$(this).dialog('close');
						}
					},
					{
						'text': '<?=t("Delete")?>',
						'class': 'btn pull-right btn-danger',
						'click': function() {
							$('form[data-delete-form=' + ptID + ']').submit();
						}
					}
				]
			});
		});
	});
	</script>

<? } ?>