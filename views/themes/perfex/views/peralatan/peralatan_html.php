<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="peralatan-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="col-md-3">
                  <h3 class="bold no-mtop peralatan-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_peralatan_number($peralatan->id); ?>
                     </span>
                  </h3>
                  <h4 class="peralatan-html-status mtop7">
                     <?php echo format_peralatan_status($peralatan->status,'',true); ?>
                  </h4>
               </div>
               <div class="col-md-9">         
                  <?php
                     // Is not accepted, declined and expired
                     if ($peralatan->status != 4 && $peralatan->status != 3 && $peralatan->status != 5) {
                       $can_be_accepted = true;
                       if($identity_confirmation_enabled == '0'){
                         echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                         echo form_hidden('peralatan_action', 4);
                         echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_peralatan').'</button>';
                         echo form_close();
                       } else {
                         echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_peralatan').'</button>';
                       }
                     } else if($peralatan->status == 3){
                       if (($peralatan->open_till >= date('Y-m-d') || !$peralatan->open_till) && $peralatan->status != 5) {
                         $can_be_accepted = true;
                         if($identity_confirmation_enabled == '0'){
                           echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                           echo form_hidden('peralatan_action', 4);
                           echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_peralatan').'</button>';
                           echo form_close();
                         } else {
                           echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_peralatan').'</button>';
                         }
                       }
                     }
                     // Is not accepted, declined and expired
                     if ($peralatan->status != 4 && $peralatan->status != 3 && $peralatan->status != 5) {
                       echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                       echo form_hidden('peralatan_action', 3);
                       echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_peralatan').'</button>';
                       echo form_close();
                     }
                     ?>
                  <?php echo form_open(site_url('peralatan/pdf/'.$peralatan->id), array('class'=>'pull-right action-button')); ?>
                  <button type="submit" name="peralatanpdf" class="btn btn-default action-button download mright5 mtop7" value="peralatanpdf">
                  <i class="fa fa-file-pdf-o"></i>
                  <?php echo _l('clients_invoice_html_btn_download'); ?>
                  </button>
                  <?php echo form_close(); ?>
                  <?php if((is_client_logged_in() && has_contact_permission('peralatan'))  || is_staff_member()){ ?>
                  <a href="<?php echo site_url('clients/peralatan/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
                  <?php } ?>
               </div>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>

   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold peralatan-html-number"><?php echo format_peralatan_number($peralatan->id); ?></h4>
               <address class="peralatan-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold peralatan_to"><?php echo _l('peralatan_to'); ?>:</span>
               
                  <address class="no-margin peralatan-html-info">
                     <?php echo format_peralatan_info($peralatan, 'html'); ?>
                  </address>
               <p class="no-mbot peralatan-html-date">
                  <span class="bold">
                  <?php echo _l('peralatan_data_date'); ?>:
                  </span>
                  <?php echo _d($peralatan->date); ?>
               </p>
               <?php if(!empty($peralatan->open_till)){ ?>
               <p class="no-mbot peralatan-html-expiry-date">
                  <span class="bold"><?php echo _l('peralatan_data_expiry_date'); ?></span>:
                  <?php echo _d($peralatan->open_till); ?>
               </p>
               <?php } ?>
               <?php if(!empty($peralatan->reference_no)){ ?>
               <p class="no-mbot peralatan-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $peralatan->reference_no; ?>
               </p>
               <?php } ?>

               <?php $pdf_custom_fields = get_custom_fields('peralatan',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($peralatan->id,$field['id'],'peralatan');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_items_table_data($peralatan, 'peralatan');
                     echo $items->table();
                     ?>
               </div>
            </div>
            <div class="col-md-6 col-md-offset-6">
               <table class="table text-right">
                  <tbody>
                     <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('peralatan_subtotal'); ?></span>
                        </td>
                        <td class="subtotal">
                           <?php echo app_format_money($peralatan->subtotal, $peralatan->currency_name); ?>
                        </td>
                     </tr>
                     <?php if(is_sale_discount_applied($peralatan)){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('peralatan_discount'); ?>
                           <?php if(is_sale_discount($peralatan,'percent')){ ?>
                           (<?php echo app_format_number($peralatan->discount_percent,true); ?>%)
                           <?php } ?></span>
                        </td>
                        <td class="discount">
                           <?php echo '-' . app_format_money($peralatan->discount_total, $peralatan->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <?php
                        foreach($items->taxes() as $tax){
                         echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $peralatan->currency_name).'</td></tr>';
                        }
                        ?>
                     <?php if((int)$peralatan->adjustment != 0){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('peralatan_adjustment'); ?></span>
                        </td>
                        <td class="adjustment">
                           <?php echo app_format_money($peralatan->adjustment, $peralatan->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <tr>
                        <td><span class="bold"><?php echo _l('peralatan_total'); ?></span>
                        </td>
                        <td class="total">
                           <?php echo app_format_money($peralatan->total, $peralatan->currency_name); ?>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <?php
               if(get_option('peralatan_total_to_words_enabled') == 1){ ?>
            <div class="col-md-12 text-center peralatan-html-total-to-words">
               <p class="bold"><?php echo  _l('num_word').' : '.$this->numberword->convert($peralatan->total,$peralatan->currency_name); ?></p>
            </div>
            <?php } ?>
            <?php if(count($peralatan->attachments) > 0 && $peralatan->visible_attachments_to_customer_found == true){ ?>
            <div class="clearfix"></div>
            <div class="peralatan-html-files">
               <div class="col-md-12">
                  <hr />
                  <p class="bold mbot15 font-medium"><?php echo _l('peralatan_files'); ?></p>
               </div>
               <?php foreach($peralatan->attachments as $attachment){
                  // Do not show hidden attachments to customer
                  if($attachment['visible_to_customer'] == 0){continue;}
                  $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                  if(!empty($attachment['external'])){
                  $attachment_url = $attachment['external_link'];
                  }
                  ?>
               <div class="col-md-12 mbot15">
                  <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                  <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
               </div>
               <?php } ?>
            </div>
            <?php } ?>
            <?php if(!empty($peralatan->client_note)){ ?>
            <div class="col-md-12 peralatan-html-note">
               <b><?php echo _l('peralatan_note'); ?></b><br /><br /><?php echo $peralatan->client_note; ?>
            </div>
            <?php } ?>
            <?php if(!empty($peralatan->terms)){ ?>
            <div class="col-md-12 peralatan-html-terms-and-conditions">
               <hr />
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br /><br /><?php echo $peralatan->terms; ?>
            </div>
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('peralatan_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
