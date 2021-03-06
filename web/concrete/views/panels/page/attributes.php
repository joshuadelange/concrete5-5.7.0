<?
defined('C5_EXECUTE') or die("Access Denied.");
?>

<section id="ccm-menu-page-attributes">
	<header><a href="" data-panel-navigation="back" class="ccm-panel-back"><span class="fa fa-chevron-left"></span></a> <?=t('Attributes')?></header>
	<div class="ccm-menu-page-attributes-search">
		<i class="fa fa-search"></i>
		<input type="text" name="" id="ccm-menu-page-attributes-search-input" placeholder="<?=t('Search')?>" autocomplete="false" />
	</div>

	<div class="ccm-panel-content-inner" id="ccm-menu-page-attributes-list">
	<? foreach($attributes as $set) { ?>
		<div class="ccm-menu-page-attributes-set">
			<h5><?=$set->title?></h5>
			<ul>
			<? foreach($set->attributes as $key) { ?>
				<li><a data-attribute-key="<?=$key->getAttributeKeyID()?>" <? if (in_array($key->getAttributeKeyID(), $selectedAttributeIDs)) { ?>class="ccm-menu-page-attribute-selected" <? } ?> href="#"><?=$key->getAttributeKeyName()?></a></li>
			<? } ?>
			</ul>
		</div>
	<? } ?>
	</div>

</section>


<script type="text/javascript">
ConcreteMenuPageAttributes = {

	selectAttributeKey: function(akID) {
		$attribute = $('a[data-attribute-key=' + akID + ']');
		$attribute.addClass('ccm-menu-page-attribute-selected');
		ConcretePageAttributesDetail.addAttributeKey(akID);
	},

	deselectAttributeKey: function(akID) {
		$attribute = $('a[data-attribute-key=' + akID + ']');
		$attribute.removeClass('ccm-menu-page-attribute-selected');
	},

}
$(function() {
	$('#ccm-menu-page-attributes-search-input').liveUpdate('ccm-menu-page-attributes-list', 'attributes');
	$('#ccm-menu-page-attributes').on('click', 'a[data-attribute-key]:not(.ccm-menu-page-attribute-selected)', function() {
		ConcreteMenuPageAttributes.selectAttributeKey($(this).attr('data-attribute-key'));
	});
});
</script>
