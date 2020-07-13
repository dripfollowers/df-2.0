jQuery(document).ready(function ($) {
	
	Labels = {
		MANUAL_ACTIONS 	: 'Manual Actions',
		MARK_AS_DONE 	: 'Mark as done & Notify',
		MARK_AS_UNDONE 	: 'Mark as undone',
		NOTICE_OF_START : 'Notice of start',
		MARK_AS_VALID	: 'Mark Payment as VALID',
		EXPORT			: 'Export',
		ARE_YOU_SURE 	: 'Are you sure?',
		DONE 			: 'DONE',
		TASK_CANCEL		: 'Cancel task',
		TASK_RESUME		: 'Resume task',
		CANCELED		: 'CANCELED',
		IN_PROGRESS		: 'IN PROGRESS',
		TRANSFER_IGERS  : 'Transfer to IgersLike',
		TRANSFER_OTL  : 'Transfer to Otl',
		UNDONE_ACTION 	: 'Use carefully, this action can not be undone! Are you sure again?',
	}
	
	Actions = {
		MARK_AS_DONE 	: 'mark_as_done',
		MARK_AS_UNDONE 	: 'mark_as_undone',
		NOTICE_OF_START : 'notice_of_start',
		DO_FOR_ORDERS 	: 'do_for_orders',
		EXPORT_ORDERS 	: 'export_orders',
		MARK_AS_VALID	: 'mark_as_valid',
		TASK_CANCEL		: 'task_cancel',
		TASK_RESUME		: 'task_resume',
		TRANSFER_IGERS  : 'transfer_igers',
		TRANSFER_OTL	: 'transfer_otl'
	}
	
	var addAction = function (action, label){
		var $action = $('<option>').val(action).text(label);
		$action.clone().appendTo('select[name=action]');
		$action.clone().appendTo('select[name=action2]');
	}
	
	$('select[name=action] option[value=-1]').html(Labels.MANUAL_ACTIONS);
	$('select[name=action2] option[value=-1]').html(Labels.MANUAL_ACTIONS);
	
	$('li.publish').remove();
	$('select[name=seo_filter]').remove();
	$('div.view-switch').remove();
	$('div[class="tablenav-pages one-page"]').remove();
	$('div.bulkactions option[value=trash]').remove();
	$('div.bulkactions option[value=edit]').remove();
	
	$spinner = $('<span class="spinner" style="display: inline;"></span>');
	$spinner.hide();
	$('#doaction, #doaction2').after($spinner);
	
	addAction(Actions.MARK_AS_DONE, Labels.MARK_AS_DONE);
	addAction(Actions.MARK_AS_UNDONE, Labels.MARK_AS_UNDONE);
	addAction(Actions.NOTICE_OF_START, Labels.NOTICE_OF_START);
	addAction(Actions.MARK_AS_VALID, Labels.MARK_AS_VALID);
	addAction(Actions.TASK_CANCEL, Labels.TASK_CANCEL);
	//Not working well, this is an API issue, note mine!
	addAction(Actions.TASK_RESUME, Labels.TASK_RESUME);
	addAction(Actions.TRANSFER_IGERS, Labels.TRANSFER_IGERS);
	addAction(Actions.TRANSFER_OTL, Labels.TRANSFER_OTL);
	
	$exportBtn = $('<input type="submit" id="'+Actions.EXPORT_ORDERS+'" class="button" value="'+ Labels.EXPORT +'" />');
	$exportBtn.insertAfter($('#post-query-submit'));
	
	$exportBtn.click(function(e){
    	e.preventDefault();
    	var m = $('select[name=m]').val();
    	window.open(ajaxurl+'?action='+Actions.EXPORT_ORDERS+'&m='+m);
	});
	
    $('#doaction, #doaction2').click(function(e){
    	e.preventDefault();
    	
    	var choice = $('select[name=action]').val()!=-1?$('select[name=action]').val():$('select[name=action2]').val();
    	
	    if(choice!=-1){
			var orders = [];
			$("[name='post[]']:checked").each(function(){
				orders.push($(this).val());
			});
			
			if(orders.length>0) {
				var r = confirm(Labels.ARE_YOU_SURE);
				if (r==true) {
					$('.spinner').show();
					var data = {
							action: Actions.DO_FOR_ORDERS,
							orders: orders,
							choice: choice
					};
					
					var jqxhr = $.post( ajaxurl, data, function(result) {
						var response = JSON.parse(result);
						if(choice == Actions.MARK_AS_DONE || choice == Actions.MARK_AS_UNDONE){
							var progress = choice==Actions.MARK_AS_DONE? Labels.DONE:'-';
							if(response.success){
								for (var i = 0; i < response.success.length; i++) {
									var $td = $('#post-'+response.success[i]+' td.order-progress');
									$td.html(progress);
									var $tr = $td.parent();
									var color = choice==Actions.MARK_AS_DONE?'#70FF94':'#FF9470';;
									$tr.css('background-color', color);
									if(choice==Actions.MARK_AS_DONE){
										var $remarks = $('#post-'+response.success[i]+' td.order-remarks');
										$remarks.html('');
									}
								}
							}
						} else if(choice == Actions.TASK_CANCEL || choice == Actions.TASK_RESUME || choice == Actions.TRANSFER_IGERS){
							var progress = choice==Actions.TASK_CANCEL? Labels.CANCELED:Labels.IN_PROGRESS;
							if(response.success){
								for (var i = 0; i < response.success.length; i++) {
									var $td = $('#post-'+response.success[i]+' td.order-progress');
									$td.html(progress);
									var $tr = $td.parent();
									var color = (choice==Actions.TASK_RESUME || choice == Actions.TRANSFER_IGERS)?'#70FF94':'#FF9470';;
									$tr.css('background-color', color);
									if (choice == Actions.TRANSFER_IGERS) {
										var $provider = $('#post-'+response.success[i]+' td.order-provider');
										$provider.html('igers');										
									}
								}
							}
						}
						else if(choice == Actions.NOTICE_OF_START || choice == Actions.MARK_AS_VALID){
							if(response.errors){
								for (var i = 0; i < response.errors.length; i++) {
									var $tr = $('#post-'+response.errors[i]);
									$tr.css('background-color', '#FF9470');
								}
								if(choice == Actions.NOTICE_OF_START){
									alert('Some errors occured while performing your request');
								}
							}
							if(response.success){
								for (var i = 0; i < response.success.length; i++) {
									var $tr = $('#post-'+response.success[i]);
									$tr.css('background-color', '#70FF94');
									if(choice == Actions.MARK_AS_VALID){
										var $td = $tr.find('td.order-payment-status');
										$td.html('VALID');
									}
								}
							}
						} 
					})
					.fail(function() {
						location.reload(true);
					})
					.always(function() {
						$('.spinner').hide();
						alert('Operation Accomplished');
					});
				}
			} 
    	}
    });
});