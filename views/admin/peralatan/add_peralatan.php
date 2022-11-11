<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content accounting-template peralatan">
    <div class="row">
      <?php
      if (isset($peralatan)) {
        echo form_hidden('isedit', $peralatan->id);
      }
      $clientid = '';
      if (isset($peralatan) || $this->input->get('clientid')) {
        if ($this->input->get('clientid')) {
          $clientid = $this->input->get('clientid');
        } else {
          $clientid = $peralatan->clientid;
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
                <div class="form-group select-placeholder" id="clientid_wrapper">
                  <div class="form-group select-placeholder">
                    <label for="clientid" class="control-label"><?php echo _l('peralatan_select_customer'); ?></label>
                    <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($peralatan) && empty($peralatan->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                   <?php $selected = (isset($peralatan) ? $peralatan->clientid : '');
                     if($selected == ''){
                       $selected = (isset($customer_id) ? $customer_id: '');
                     }
                     if($selected != ''){
                        $rel_data = get_relation_data('customer',$selected);
                        $rel_val = get_relation_values($rel_data,'customer');
                        echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                     } ?>
                    </select>
                  </div>
                </div>
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
                <?php
                $selected = '';
                $currency_attr = array('data-show-subtext' => true);
                foreach ($currencies as $currency) {
                  if ($currency['isdefault'] == 1) {
                    $currency_attr['data-base'] = $currency['id'];
                  }
                  if (isset($peralatan)) {
                    if ($currency['id'] == $peralatan->currency) {
                      $selected = $currency['id'];
                    }
                    if ($peralatan->rel_type == 'customer') {
                      $currency_attr['disabled'] = true;
                    }
                  } else {

                      $customer_currency = $this->clients_model->get_customer_default_currency($clientid);
                      if ($customer_currency != 0) {
                        $selected = $customer_currency;
                      } else {
                        if ($currency['isdefault'] == 1) {
                          $selected = $currency['id'];
                        }
                      }
                      $currency_attr['disabled'] = true;
                  }
                }
                $currency_attr = apply_filters_deprecated('peralatan_currency_disabled', [$currency_attr], '2.3.0', 'peralatan_currency_attributes');
                $currency_attr = hooks()->apply_filters('peralatan_currency_attributes', $currency_attr);
                ?>
                <div class="row">
                  <div class="col-md-6">
                    <?php
                    echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'peralatan_currency', $selected, $currency_attr);
                    ?>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                      <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value="" selected><?php echo _l('no_discount'); ?></option>
                        <option value="before_tax" <?php
                                                    if (isset($peralatan)) {
                                                      if ($peralatan->discount_type == 'before_tax') {
                                                        echo 'selected';
                                                      }
                                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                        <option value="after_tax" <?php if (isset($peralatan)) {
                                                    if ($peralatan->discount_type == 'after_tax') {
                                                      echo 'selected';
                                                    }
                                                  } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                      </select>
                    </div>
                  </div>
                </div>
                <?php $fc_rel_id = (isset($peralatan) ? $peralatan->id : false); ?>
                <?php echo render_custom_fields('peralatan', $fc_rel_id); ?>
                <div class="form-group no-mbot">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($peralatan) ? prep_tags_input(get_tags_in($peralatan->id, 'peralatan')) : ''); ?>" data-role="tagsinput">
                </div>
                <div class="form-group mtop10 no-mbot">
                  <p><?php echo _l('peralatan_allow_comments'); ?></p>
                  <div class="onoffswitch">
                    <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if ((isset($peralatan) && $peralatan->allow_comments == 1) || !isset($peralatan)) {
                                                                                              echo 'checked';
                                                                                            }; ?> value="on" name="allow_comments">
                    <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip" title="<?php echo _l('peralatan_allow_comments_help'); ?>"></label>
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
                  <div class="col-md-6">
                    <?php
                    $i = 0;
                    $selected = '';
                    foreach ($staff as $member) {
                      if (isset($peralatan)) {
                        if ($peralatan->assigned == $member['staffid']) {
                          $selected = $member['staffid'];
                        }
                      }
                      $i++;
                    }
                    echo render_select('assigned', $staff, array('staffid', array('firstname', 'lastname')), 'peralatan_assigned', $selected);
                    ?>
                  </div>
                </div>
                <?php $value = (isset($peralatan) ? $peralatan->peralatan_to : ''); ?>
                <?php echo render_input('peralatan_to', 'peralatan_to', $value); ?>
                <?php $value = (isset($peralatan) ? $peralatan->address : ''); ?>
                <?php echo render_textarea('address', 'peralatan_address', $value); ?>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->city : ''); ?>
                    <?php echo render_input('city', 'billing_city', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->state : ''); ?>
                    <?php echo render_input('state', 'billing_state', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $countries = get_all_countries(); ?>
                    <?php $selected = (isset($peralatan) ? $peralatan->country : ''); ?>
                    <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $selected); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->zip : ''); ?>
                    <?php echo render_input('zip', 'billing_zip', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->email : ''); ?>
                    <?php echo render_input('email', 'peralatan_email', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($peralatan) ? $peralatan->phone : ''); ?>
                    <?php echo render_input('phone', 'peralatan_phone', $value); ?>
                  </div>
                </div>
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
      <div class="col-md-12">
        <div class="panel_s">
          <?php $this->load->view('admin/peralatan/_add_edit_items'); ?>
        </div>
      </div>
      <?php echo form_close(); ?>
      <?php $this->load->view('admin/jenis_pesawat/item'); ?>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  var _rel_id = $('#clientid'),
    _clientid_wrapper = $('#clientid_wrapper'),
    data = {};

  $(function() {
    init_currency();
    // Maybe items ajax search
    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    validate_peralatan_form();
    $('body').on('change', '#clientid', function() {
      if ($(this).val() != '') {
        $.get(admin_url + 'peralatan/get_relation_data_values/' + $(this).val(), function(response) {
          $('input[name="peralatan_to"]').val(response.to);
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


  function validate_peralatan_form() {
    appValidateForm($('#peralatan-form'), {
      subject: 'required',
      peralatan_to: 'required',
      clientid: 'required',
      date: 'required',
      email: {
        email: true,
        required: true
      },
      currency: 'required',
    });
  }
</script>
</body>

</html>