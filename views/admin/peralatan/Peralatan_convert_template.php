<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade peralatan-convert-modal" id="convert_to_peralatan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xxl" role="document">
        <?php echo form_open('admin/peralatan/convert_to_peralatan/'.$peralatan->id,array('id'=>'peralatan_convert_to_peralatan_form','class'=>'_transaction_form disable-on-submit')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="close_modal_manually('#convert_to_peralatan')" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('peralatan_convert_to_peralatan'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php $this->load->view('admin/peralatan/peralatan_template'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="close_modal_manually('#convert_to_peralatan')">
                    <?php echo _l('close'); ?>
                </button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php $this->load->view('admin/jenis_pesawat/item'); ?>
<script>
    init_ajax_search('customer','#clientid.ajax-search');
    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
    custom_fields_hyperlink();
    init_selectpicker();
    init_datepicker();
    init_color_pickers();
    init_items_sortable();
    init_tags_inputs();
    validate_peralatan_form('#peralatan_convert_to_peralatan_form');
    <?php if($peralatan->assigned != 0){ ?>
    $('#convert_to_peralatan #sale_agent').selectpicker('val',<?php echo $peralatan->assigned; ?>);
    <?php } ?>
    $('select[name="discount_type"]').selectpicker('val','<?php echo $peralatan->discount_type; ?>');
    $('input[name="discount_percent"]').val('<?php echo $peralatan->discount_percent; ?>');
    $('input[name="discount_total"]').val('<?php echo $peralatan->discount_total; ?>');
    <?php if(is_sale_discount($peralatan,'fixed')) { ?>
        $('.discount-total-type.discount-type-fixed').click();
    <?php } ?>
    $('input[name="adjustment"]').val('<?php echo $peralatan->adjustment; ?>');
    $('input[name="show_quantity_as"][value="<?php echo $peralatan->show_quantity_as; ?>"]').prop('checked',true).change();
    $('#convert_to_peralatan #clientid').change();
    // Trigger item select width fix
    $('#convert_to_peralatan').on('shown.bs.modal', function(){
        $('#item_select').trigger('change')
    })

</script>
