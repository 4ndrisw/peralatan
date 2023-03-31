// Init single peralatan
function init_peralatan(id) {
    load_small_table_item(id, '#peralatan', 'peralatan_id', 'peralatan/get_peralatan_data_ajax', '.table-peralatan');
}

/*
if ($("body").hasClass('peralatan-pipeline')) {
    var peralatan_id = $('input[name="peralatan_id"]').val();
    peralatan_pipeline_open(peralatan_id);
}
*/

// peralatan quick total stats
function init_peralatan_total(manual) {

    if ($('#peralatan_total').length === 0) {
        return;
    }
    var _est_total_href_manual = $('.peralatan-total');
    if ($("body").hasClass('peralatan-total-manual') && typeof (manual) == 'undefined' &&
        !_est_total_href_manual.hasClass('initialized')) {
        return;
    }
    _est_total_href_manual.addClass('initialized');
    var currency = $("body").find('select[name="total_currency"]').val();
    var _years = $("body").find('select[name="peralatan_total_years"]').selectpicker('val');
    var years = [];
    $.each(_years, function (i, _y) {
        if (_y !== '') {
            years.push(_y);
        }
    });

    var customer_id = '';
    var project_id = '';

    var _customer_id = $('.customer_profile input[name="userid"]').val();
    var _project_id = $('input[name="project_id"]').val();
    if (typeof (_customer_id) != 'undefined') {
        customer_id = _customer_id;
    } else if (typeof (_project_id) != 'undefined') {
        project_id = _project_id;
    }

    $.post(admin_url + 'peralatan/get_peralatan_total', {
        currency: currency,
        init_total: true,
        years: years,
        customer_id: customer_id,
        project_id: project_id,
    }).done(function (response) {
        $('#peralatan_total').html(response);
    });
}

function add_peralatan_comment() {
    var comment = $('#comment').val();
    if (comment == '') {
        return;
    }
    var data = {};
    data.content = comment;
    data.peralatan_id = peralatan_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'peralatan/add_peralatan_comment', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_peralatan_comments();
        }
    });
}

function get_peralatan_comments() {
    if (typeof (peralatan_id) == 'undefined') {
        return;
    }
    requestGet('peralatan/get_peralatan_comments/' + peralatan_id).done(function (response) {
        $('body').find('#peralatan-comments').html(response);
        update_comments_count('peralatan')
    });
}

function remove_peralatan_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('peralatan/remove_comment/' + commentid).done(function (response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
                update_comments_count('peralatan')
            }
        });
    }
}

function edit_peralatan_comment(id) {
    var content = $('body').find('[data-peralatan-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'peralatan/edit_comment/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-peralatan-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_peralatan_comment_edit(id);
    }
}

function toggle_peralatan_comment_edit(id) {
    $('body').find('[data-peralatan-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-peralatan-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}

function peralatan_convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'peralatan') {
        html_helper_selector = 'peralatan';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }

    requestGet('peralatan/get_' + html_helper_selector + '_convert_data/' + peralatan_id).done(function (data) {
        if ($('.peralatan-pipeline-modal').is(':visible')) {
            $('.peralatan-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({
            show: true,
            backdrop: 'static'
        });
        reorder_items();
    });

}

function save_peralatan_content(manual) {
    var editor = tinyMCE.activeEditor;
    var data = {};
    data.peralatan_id = peralatan_id;
    data.content = editor.getContent();
    $.post(admin_url + 'peralatan/save_peralatan_data', data).done(function (response) {
        response = JSON.parse(response);
        if (typeof (manual) != 'undefined') {
            // Show some message to the user if saved via CTRL + S
            alert_float('success', response.message);
        }
        // Invokes to set dirty to false
        editor.save();
    }).fail(function (error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

// Proposal sync data in case eq mail is changed, shown for lead and customers.
function sync_peralatan_data(clientid, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_peralatan_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.clientid = clientid;
    data.rel_type = rel_type;
    $.post(admin_url + 'peralatan/sync_data', data).done(function (response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}


// Delete peralatan attachment
function delete_peralatan_attachment(id) {
    if (confirm_delete()) {
        requestGet('peralatan/delete_attachment/' + id).done(function (success) {
            if (success == 1) {
                var clientid = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('peralatan-pipeline') ? peralatan_pipeline_open(clientid) : init_peralatan(clientid);
            }
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Used when peralatan is updated from pipeline. eq changed order or moved to another status
function peralatan_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            peralatan_id: $(ui.item).attr('data-peralatan-id'),
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            order: [],
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-peralatan-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-peralatan-id]');

        setTimeout(function () {
             $.post(admin_url + 'peralatan/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                peralatan_pipeline();
            });
        }, 200);
    }
}

// Used when peralatan is updated from pipeline. eq changed order or moved to another status
function peralatan_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            order: [],
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            peralatan_id: $(ui.item).attr('data-peralatan-id'),
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-peralatan-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-peralatan-id]');

        setTimeout(function () {
            $.post(admin_url + 'peralatan/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                peralatan_pipeline();
            });
        }, 200);
    }
}

// Init peralatan pipeline
function peralatan_pipeline() {
    init_kanban('peralatan/get_pipeline', peralatan_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single peralatan in pipeline
function peralatan_pipeline_open(id) {
    if (id === '') {
        return;
    }
    requestGet('peralatan/pipeline_open/' + id).done(function (response) {
        var visible = $('.peralatan-pipeline-modal:visible').length > 0;
        $('#peralatan').html(response);
        if (!visible) {
            $('.peralatan-pipeline-modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        } else {
            $('#peralatan').find('.modal.peralatan-pipeline-modal')
                .removeClass('fade')
                .addClass('in')
                .css('display', 'block');
        }
    });
}

// Sort peralatan in the pipeline view / switching sort type by click
function peralatan_pipeline_sort(type) {
    kan_ban_sort(type, peralatan_pipeline);
}

// Validates peralatan add/edit form
function validate_peralatan_form(selector) {

    selector = typeof (selector) == 'undefined' ? '#peralatan-form' : selector;

    appValidateForm($(selector), {
        clientid: {
            required: {
                depends: function () {
                    var customerRemoved = $('select#clientid').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        office_id: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "peralatan/validate_peralatan_number",
            type: 'post',
            data: {
                number: function () {
                    return $('input[name="number"]').val();
                },
                isedit: function () {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function () {
                    return $('input[name="number"]').data('original-number');
                },
                date: function () {
                    return $('body').find('.peralatan input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.peralatan_number_exists,
        }
    });

}


// Get the preview main values
function get_peralatan_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    return response;
}

// Append the added items to the preview to the table as items
/*
function add_peralatan_item_to_table(data, itemid){

  // If not custom data passed get from the preview
  data = typeof (data) == 'undefined' || data == 'undefined' ? get_peralatan_item_preview_values() : data;
  if (data.description === "" && data.long_description === "") {
     return;
  }

  var table_row = '';
  var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('tbody .item').length + 1;
  lastAddedItemKey = item_key;

  table_row += '<tr class="sortable item">';

  table_row += '<td class="dragger">';

  // Check if quantity is number
  if (isNaN(data.qty)) {
     data.qty = 1;
  }

  $("body").append('<div class="dt-loader"></div>');
  var regex = /<br[^>]*>/gi;

     table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

     table_row += '</td>';

     table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

     table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';
   //table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description + '</textarea></td>';


     table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

     if (!data.unit || typeof (data.unit) == 'undefined') {
        data.unit = '';
     }

     table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

     table_row += '</td>';


     table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

     table_row += '</tr>';

     $('table.items tbody').append(table_row);

     $(document).trigger({
        type: "item-added-to-table",
        data: data,
        row: table_row
     });


     clear_item_preview_values();
     reorder_items();

     $('body').find('#items-warning').remove();
     $("body").find('.dt-loader').remove();

  return false;
}
*/


// From peralatan table mark as
function peralatan_mark_action_status(status_id, peralatan_id) {
    var data = {};
    data.status = status_id;
    data.peralatan_id = peralatan_id;
    $.post(admin_url + 'peralatan/mark_action_status/' + status_id +'/'+ peralatan_id).done(function (response) {
        //table_peralatan.DataTable().ajax.reload(null, false);
        reload_peralatan_tables();
    });
}



// Reload all peralatan possible table where the table data needs to be refreshed after an action is performed on task.
function reload_peralatan_tables() {
    var av_peralatan_tables = ['.table-peralatan', '.table-rel-peralatan'];
    $.each(av_peralatan_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}
