<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content accounting-template peralatan">
    <div class="row">
         <?php
         if(isset($peralatan)){
             echo form_hidden('isedit',$peralatan->id);
            }
            $rel_type = '';
            $clientid = '';
            if(isset($peralatan) || ($this->input->get('clientid') && $this->input->get('rel_type'))){
             if($this->input->get('clientid')){
               $clientid = $this->input->get('clientid');
               $rel_type = $this->input->get('rel_type');
             } else {
               $clientid = $peralatan->clientid;
               $rel_type = $peralatan->rel_type;
             }
            }
            ?>
         <?php
      echo form_open($this->uri->uri_string(), array('id' => 'peralatan-form', 'class' => '_transaction_form peralatan-form'));

      if ($this->input->get('peralatan_request_id')) {
        echo form_hidden('peralatan_request_id', $this->input->get('peralatan_request_id'));
      }
      ?>

      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <?php if (isset($peralatan)) { ?>
                <div class="col-md-12">
                  <?php echo format_peralatan_status($peralatan->status); ?>
                </div>
                <div class="clearfix"></div>
                <hr />
              <?php } ?>
              <div class="col-md-6 border-right">
                <?php $value = (isset($peralatan) ? $peralatan->subject : ''); ?>
                <?php $attrs = (isset($peralatan) ? array() : array('autofocus' => true)); ?>
                <?php echo render_input('subject', 'peralatan_subject', $value, 'text', $attrs); ?>
                
                
                <?php if(strtolower($client_type) !== 'company') { ?>
                  <div class="form-group select-placeholder" id="rel_id_wrapper">
                    <div class="form-group select-placeholder">
                      <label for="client_id" class="control-label"><?php echo _l('peralatan_select_customer'); ?></label>
                      <select id="client_id" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($peralatan) && empty($peralatan->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                     <?php $selected = (isset($peralatan) ? $peralatan->client_id : '');
                       if($selected == ''){
                         $selected = (isset($customer_id) ? $customer_id: '');
                       }
                       if($selected != ''){
                          $rel_data = apps_get_relation_data('companies',$selected);
                          $rel_val = apps_get_relation_values($rel_data,'companies');
                          echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                       } 

                       ?>
                      </select>
                    </div>
                  </div>
                <?php } ?> 


                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? _d($peralatan->date) : _d(date('Y-m-d'))) ?>
                    <?php echo render_date_input('date', 'peralatan_date', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php
                    $value = '';
                    if (isset($peralatan)) {
                      $value = _d($peralatan->open_till);
                    } else {
                      if (get_option('peralatan_due_after') != 0) {
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('peralatan_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                      }
                    }
                    echo render_date_input('open_till', 'peralatan_open_till', $value); ?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->nomor_seri : ''); ?>
                    <?php echo render_input('nomor_seri', 'nomor_seri', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->nomor_unit : ''); ?>
                    <?php echo render_input('nomor_unit', 'nomor_unit', $value); ?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php
                    $i = 0;
                    $selected = '';
                    foreach ($jenis_pesawat as $pesawat) {
                      if (isset($peralatan)) {
                        if ($peralatan->jenis_pesawat_id == $pesawat['id']) {
                          $selected = $pesawat['id'];
                        }
                      }
                      $i++;
                    }
                    echo render_select('jenis_pesawat_id', $jenis_pesawat, array('id', array('description',)), 'peralatan_jenis_pesawat', $selected);
                    ?>
                  </div>
                </div>

              </div>
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="status" class="control-label"><?php echo _l('peralatan_status'); ?></label>
                      <?php
                      $disabled = '';
                      if (isset($peralatan)) {
                        if ($peralatan->id != NULL || $peralatan->invoice_id != NULL) {
                          $disabled = 'disabled';
                        }
                      }
                      ?>
                      <select name="status" class="selectpicker" data-width="100%" <?php echo $disabled; ?> data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php foreach ($statuses as $status) { ?>
                          <option value="<?php echo $status; ?>" <?php if ((isset($peralatan) && $peralatan->status == $status) || (!isset($peralatan) && $status == 0)) {
                                                                    echo 'selected';
                                                                  } ?>><?php echo format_peralatan_status($status, '', false); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-6 form-group mtop10 no-mbot">
                    <p><?php echo _l('peralatan_allow_comments'); ?></p>
                    <div class="onoffswitch">
                      <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if ((isset($peralatan) && $peralatan->allow_comments == 1) || !isset($peralatan)) {
                                                                                                echo 'checked';
                                                                                              }; ?> value="on" name="allow_comments">
                      <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip" title="<?php echo _l('peralatan_allow_comments_help'); ?>"></label>
                    </div>
                  </div>

                </div>
                <?php $value = (isset($peralatan) ? $peralatan->peralatan_to : ''); ?>
                <?php if(strtolower($client_type) !== 'company') { ?>
                  <?php echo render_input('peralatan_to', 'peralatan_to', $value); ?>
                <?php } ?>

                <?php $value = (isset($peralatan) ? $peralatan->lokasi : ''); ?>
                <?php echo render_textarea('lokasi', 'peralatan_lokasi', $value); ?>

              </div>
            </div>
            <div class="btn-bottom-toolbar bottom-transaction text-right">
              <p class="no-mbot pull-left mtop5 btn-toolbar-notice"><?php echo _l('include_peralatan_items_merge_field_help', '<b>{peralatan_items}</b>'); ?></p>
              <button type="button" class="btn btn-info mleft10 peralatan-form-submit save-and-send transaction-submit">
                <?php echo _l('save_and_send'); ?>
              </button>
              <button class="btn btn-info mleft5 peralatan-form-submit transaction-submit" type="button">
                <?php echo _l('submit'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  var _clientid = $('#client_id'),
    _clientid_wrapper = $('#rel_id_wrapper'),
    data = {};
  $(function() {

    // On document read check and init for client ajax-search
    apps_ajax_search("companies", "#client_id.ajax-search");

    validate_peralatan_form();
    $('body').on('change', '#client_id', function() {
      if ($(this).val() != '') {
        $.get(admin_url + 'peralatan/get_relation_data_values/' + $(this).val(), function(response) {
          $('input[name="peralatan_to"]').val(response.to);
          $('textarea[name="lokasi"]').val(response.lokasi);
          $('textarea[name="address"]').val(response.address);
          $('input[name="email"]').val(response.email);
          $('input[name="phone"]').val(response.phone);
          $('input[name="city"]').val(response.city);
          $('input[name="state"]').val(response.state);
          $('input[name="zip"]').val(response.zip);
          $('select[name="country"]').selectpicker('val', response.country);
          var currency_selector = $('#currency');
          
          if (typeof(currency_selector.attr('multi-currency')) == 'undefined') {
            currency_selector.attr('disabled', true);
          }
          var peralatan_to_wrapper = $('[app-field-wrapper="peralatan_to"]');
          if (response.is_using_company == false && !empty(response.company)) {
            peralatan_to_wrapper.find('#use_company_name').remove();
            peralatan_to_wrapper.find('#use_company_help').remove();
            peralatan_to_wrapper.append('<div id="use_company_help" class="hide">' + response.company + '</div>');
            peralatan_to_wrapper.find('label')
              .prepend("<a href=\"#\" id=\"use_company_name\" data-toggle=\"tooltip\" data-title=\"<?php echo _l('use_company_name_instead'); ?>\" onclick='document.getElementById(\"peralatan_to\").value = document.getElementById(\"use_company_help\").innerHTML.trim(); this.remove();'><i class=\"fa fa-building-o\"></i></a> ");
          } else {
            peralatan_to_wrapper.find('label #use_company_name').remove();
            peralatan_to_wrapper.find('label #use_company_help').remove();
          }
          /* Check if customer default currency is passed */
          if (response.currency) {
            currency_selector.selectpicker('val', response.currency);
          } else {
            /* Revert back to base currency */
            currency_selector.selectpicker('val', currency_selector.data('base'));
          }
          currency_selector.selectpicker('refresh');
          currency_selector.change();
        }, 'json');
      }
    });


  });
  
  /*
  function peralatan_clientid_select() {
    var serverData = {};
    serverData.clientid = _clientid.val();
    data.type = _rel_type.val();
    
    console.log(serverData);

    //apps_ajax_search(_rel_type.val(), _clientid, serverData);
    apps_ajax_search("institutions", "#client_id.ajax-search", serverData);

  }
  */
  

  function validate_peralatan_form() {
    appValidateForm($('#peralatan-form'), {
      subject: 'required',
      peralatan_to: 'required',
      clientid: 'required',
      nomor_unit: 'required',
      date: 'required',
      open_till: 'required',
      jenis_pesawat_id: 'required',
      lokasi: 'required',
      email: {
        email: true,
        required: true
      },
    });
  }
</script>
</body>

</html>