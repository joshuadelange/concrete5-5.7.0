<?
defined('C5_EXECUTE') or die("Access Denied.");
?>
<script type="text/javascript">
	
</script>
<script type="text/template" class="tbody">
<% _.each(cv.versions, function(cv) { %>
	 <%=templateRow(cv) %>
<% }); %>
</script>

<script type="text/template" class="version">
	<tr>
		<td><input type="checkbox" name="cvID[]" value="<%-cv.cvID%>" data-version-active="<%-cv.cvIsApproved == 1%>" /></td>
		<td><span class="ccm-panel-page-versions-version-id"><%-cv.cvID%></span></td>
		<td class="ccm-panel-page-versions-details">
			<p><span class="ccm-panel-page-versions-version-timestamp"><%-cv.cvDateVersionCreated%></span></p>
			<p><%-cv.cvAuthorUserName%></p>
			<% if (cv.cvComments) { %>
				<p><small><%-cv.cvComments%></small></p>
			<% } %>
		</td>
		<td>
			<% if (cv.cvIsApproved == 1) { %>
				<i class="fa fa-ok" title="<?=t('This is the approved page version.')?>"></i>
			<% } %>
			<a href="#" class="ccm-panel-page-versions-version-menu" data-launch-versions-menu="ccm-panel-page-versions-version-menu-<%-cv.cvID%>"><i class="fa fa-share-alt"></i></a>
			<div class="ccm-popover-inverse popover fade" data-menu="ccm-panel-page-versions-version-menu-<%-cv.cvID%>">
				<div class="popover-inner">
				<ul class="dropdown-menu">
					<li><% if (cv.cvIsApproved == 1) { %><span><?=t('Approve')?></span><% } else { %><a href="#" data-version-menu-task="approve" data-version-id="<%-cv.cvID%>"><?=t('Approve')?></a><% } %></li>
					<li><a href="#" data-version-menu-task="duplicate" data-version-id="<%-cv.cvID%>"><?=t('Duplicate')?></a></li>
					<li class="divider"></li>
					<li><a href="#" data-version-menu-task="new-page" data-version-id="<%-cv.cvID%>"><?=t('New Page')?></a></li>
					<% if (cv.cpCanDeletePageVersions) { %>
						<li><% if (cv.cvIsApproved == 1) { %><span><?=t('Delete')?></span><% } else { %><a href="#" data-version-menu-task="delete" data-version-id="<%-cv.cvID%>"><?=t('Delete')?></a><% } %></li>
					<% } %>
				</ul>
				</div>
			</div>
		</td>
	</tr>
</script>

<script type="text/template" class="footer">
	<tr>
		<td colspan="4">
			<ul class="pager">
				<% if (cv.hasPreviousPage == '1') { %>
					<li><a href="#" data-version-navigation="<%=cv.previousPageNum%>"><?=t('&larr; Newer')?></a></li>
				<% } else { %>
					<li class="disabled"><a href="#"><?=t('&larr; Newer')?></a></li>
				<% } %>
				<% if (cv.hasNextPage == '1') { %>
					<li><a href="#" data-version-navigation="<%=cv.nextPageNum%>"><?=t('Older &rarr;')?></a></li>
				<% } else { %>
					<li class="disabled"><a href="#"><?=t('Older &rarr;')?></a></li>
				<% } %>
			</ul>
		</td>
	</tr>
</script>

<script type="text/javascript">
var ConcretePageVersionList = {

	sendRequest: function(url, data, onComplete) {
		var _data = [];
		$.each(data, function(i, dataItem) {
			_data.push({'name': dataItem.name, 'value': dataItem.value});
		});
		$.ajax({
			type: 'post',
			dataType: 'json',
			data: _data,
			url: url,
			beforeSubmit: function() {
				jQuery.fn.dialog.showLoader();
			},
			error: function(r) {
		      ConcreteAlert.dialog('Error', '<div class="alert alert-danger">' + r.responseText + '</div>');
		  	},
			success: function(r) {
				if (r.error) {
					ConcreteAlert.dialog('Error', '<div class="alert alert-danger">' + r.errors.join("<br>") + '</div>');
				} else {
					if (onComplete) {
						onComplete(r);
					}
				}
			},
			complete: function() {
				jQuery.fn.dialog.hideLoader();
			}
		});
	},

	handleVersionRemovalResponse: function(r) {
		for (i = 0; i < r.versions.length; i++) {
			var $row = $('input[type=checkbox][value=' + r.versions[i].cvID + ']').parent().parent();
			$row.queue(function() {
				$(this).addClass('bounceOutLeft animated');
				$(this).dequeue();
			}).delay(600).queue(function() {
				$(this).remove();
				$(this).dequeue();
			});
		}
	},

	previewSelectedVersions: function(checkboxes) {
		var panel = ConcretePanelManager.getByIdentifier('page');
		if (checkboxes.length > 0) {
			var src = '<?=URL::to("/ccm/system/panels/details/page/versions")?>';
			var data = '';
			$.each(checkboxes, function(i, cb) {
				data += '&cvID[]=' + $(cb).val();
			});
			panel.openPanelDetail({'identifier': 'page-versions', 'data': data, 'url': src});

		} else {
			panel.closePanelDetail();
		}
	},

	handleVersionUpdateResponse: function(r) {
		for (i = 0; i < r.versions.length; i++) {
			var $row = $('input[type=checkbox][value=' + r.versions[i].cvID + ']').parent().parent();
			if ($row.length) {
				$row.replaceWith(templateRow(r.versions[i]));
			} else {
				$('#ccm-panel-page-versions table tbody').prepend(templateRow(r.versions[i]));
			}
			this.setupMenus();
		}
	},

	setupMenus: function() {
		// the click proxy is kinda screwy on this
		$('a.ccm-panel-page-versions-version-menu').each(function() {
			$(this).concreteMenu({
				menuLauncherHoverClass: 'ccm-panel-page-versions-hover',
				menuContainerClass: 'ccm-panel-page-versions-container',
				menu: 'div[data-menu=' + $(this).attr('data-launch-versions-menu') + ']'
			});
		});

		$('a[data-version-menu-task]').unbind('.vmenu').on('click.vmenu', function() {
			var cvID = $(this).attr('data-version-id');
			switch($(this).attr('data-version-menu-task')) {
				case 'delete':

					ConcretePageVersionList.sendRequest('<?=$controller->action("delete")?>', [{'name': 'cvID[]', 'value': cvID}], function(r) {
						ConcreteAlert.notify({
						'message': r.message
						});
						ConcretePageVersionList.handleVersionRemovalResponse(r);
					});
					break;
				case 'approve':
					ConcretePageVersionList.sendRequest('<?=$controller->action("approve")?>', [{'name': 'cvID', 'value': cvID}], function(r) {
						ConcreteAlert.notify({
						'message': r.message
						});
						ConcretePageVersionList.handleVersionUpdateResponse(r);
					});
					break;
				case 'duplicate':
					ConcretePageVersionList.sendRequest('<?=$controller->action("duplicate")?>', [{'name': 'cvID', 'value': cvID}], function(r) {
						ConcreteAlert.notify({
						'message': r.message
						});
						ConcretePageVersionList.handleVersionUpdateResponse(r);
					});
					break;
				case 'new-page':
					ConcretePageVersionList.sendRequest('<?=$controller->action("new_page")?>', [{'name': 'cvID', 'value': cvID}], function(r) {
						window.location.href = r.redirectURL;
					});
					break;
			}

			return false;
		});
	}

}

_.templateSettings.variable = "cv";

var templateBody = _.template(
    $('script.tbody').html()
);
var templateRow = _.template(
    $('script.version').html()
);
var templateFooter = _.template(
    $('script.footer').html()
);

var templateData = <?=$response->getJSON()?>;
$('#ccm-panel-page-versions table tbody').html(
	templateBody(templateData)
);
$('#ccm-panel-page-versions table tfoot').html(
	templateFooter(templateData)
);

$(function() {
	ConcretePageVersionList.setupMenus();
	$('#ccm-panel-page-versions tbody').on('click', 'tr', function() {
		var $cb = $(this).find('input[type=checkbox]');
		if (!$cb.is(':checked')) {
			$cb.prop('checked', true);
		} else {
			$cb.prop('checked', false);
		}
		$cb.trigger('change');
		return false;
	});
	$('#ccm-panel-page-versions tbody').on('click', 'input[type=checkbox]', function(e) {
		e.stopPropagation();
	});
	$('#ccm-panel-page-versions tbody').on('click', 'a.ccm-panel-page-versions-version-menu', function(e) {
		e.stopPropagation();
	});
	$('#ccm-panel-page-versions thead input[type=checkbox]').on('change', function() {
		var $checkboxes = $('#ccm-panel-page-versions tbody input[type=checkbox][data-version-active=false]');
		$checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
	});

	$('#ccm-panel-page-versions tbody').on('change', 'input[type=checkbox]', function() {
		if ($(this).is(':checked')) {
			$(this).parent().parent().addClass('ccm-panel-page-versions-version-checked');
		} else {
			$(this).parent().parent().removeClass('ccm-panel-page-versions-version-checked');
		}
		var checkboxes = $('#ccm-panel-page-versions tbody input[type=checkbox]:checked');
		$('button[data-version-action]').prop('disabled', true);
		if (checkboxes.length > 1) {
			$('button[data-version-action=compare').prop('disabled', false);
		}
		if (checkboxes.length > 0 && !checkboxes.filter('[data-version-active=true]').length) {
			$('button[data-version-action=delete').prop('disabled', false);
		}
		ConcretePageVersionList.previewSelectedVersions(checkboxes);

	});

	$('#ccm-panel-page-versions tfoot').on('click', 'a', function() {
		var pageNum = $(this).attr('data-version-navigation');
		if (pageNum) {
			ConcretePageVersionList.sendRequest('<?=$controller->action("get_json")?>', [{'name': 'currentPage', 'value': $(this).attr('data-version-navigation')}], function(r) {
				$('#ccm-panel-page-versions table tbody').html(
					templateBody(r)
		    	);
				$('#ccm-panel-page-versions table tfoot').html(
					templateFooter(r)
		    	);
			});
		}
		return false;
	});


	$('button[data-version-action=delete').on('click', function() {
		var checkboxes = $('#ccm-panel-page-versions tbody input[type=checkbox]:checked');
		var cvIDs = [];
		$.each(checkboxes, function(i, cb) {
			cvIDs.push({'name': 'cvID[]', 'value': $(cb).val()});
		});
		ConcretePageVersionList.sendRequest('<?=$controller->action("delete")?>', cvIDs, function(r) {
			ConcretePageVersionList.handleVersionRemovalResponse(r);
		});
	});

});

</script>				


<section id="ccm-panel-page-versions" class="ccm-ui">
	<header><a href="" data-panel-navigation="back" class="ccm-panel-back"><span class="fa fa-chevron-left"></span></a> <?=t('Versions')?></header>
	<table>
		<thead>
			<tr>
				<th><input type="checkbox" /></th>
				<th colspan="3"><!--<button type="button" class="btn-link" data-version-action="compare" disabled><?=t('Compare')?></button>//--><button type="button" class="btn-link" data-version-action="delete" disabled><?=t('Delete')?></button></th>
			</tr>
		</thead>
		<tbody></tbody>
		<tfoot></tfoot>
	</table>
</section>