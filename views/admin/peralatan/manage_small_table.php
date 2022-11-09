<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
               foreach($statuses as $_status){
                $val = '';
                if($_status == $this->input->get('status')){
                  $val = $_status;
                }
                echo form_hidden('peralatan_'.$_status,$val);
               }
               foreach($years as $year){
                echo form_hidden('year_'.$year['year'],$year['year']);
               }
               echo form_hidden('leads_related');
               echo form_hidden('customers_related');
               echo form_hidden('expired');
               ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if(has_permission('peralatan','','create')){ ?>
                  <a href="<?php echo admin_url('peralatan/peralatan'); ?>" class="btn btn-info pull-left display-block">
                  <?php echo _l('new_peralatan'); ?>
                  </a>
                  <?php } ?>
                  <a href="<?php echo admin_url('peralatan/pipeline/'.$switch_pipeline); ?>" class="btn btn-default mleft5 pull-left hidden-xs"><?php echo _l('switch_to_pipeline'); ?></a>
                  <div class="display-block text-right">
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-peralatan',''); return false;">
                              <?php echo _l('peralatan_list_all'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach($statuses as $status){ ?>
                           <li class="<?php if($this->input->get('status') == $status){echo 'active';} ?>">
                              <a href="#" data-cview="peralatan_<?php echo $status; ?>" onclick="dt_custom_view('peralatan_<?php echo $status; ?>','.table-peralatan','peralatan_<?php echo $status; ?>'); return false;">
                              <?php echo format_peralatan_status($status,'',false); ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php if(count($years) > 0){ ?>
                           <li class="divider"></li>
                           <?php foreach($years as $year){ ?>
                           <li class="active">
                              <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-peralatan','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php } ?>
                           
                           <li>
                              <a href="#" data-cview="expired" onclick="dt_custom_view('expired','.table-peralatan','expired'); return false;">
                              <?php echo _l('peralatan_expired'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="leads_related" onclick="dt_custom_view('leads_related','.table-peralatan','leads_related'); return false;">
                              <?php echo _l('peralatan_leads_related'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="customers_related" onclick="dt_custom_view('customers_related','.table-peralatan','customers_related'); return false;">
                              <?php echo _l('peralatan_customers_related'); ?>
                              </a>
                           </li>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-peralatan','#peralatan'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if invoiceid found in url -->
                        <?php echo form_hidden('peralatan_id',$peralatan_id); ?>
                        <?php
                           $table_data = array(
                              _l('peralatan') . ' #',
                              _l('peralatan_subject'),
                              _l('peralatan_to'),
                              _l('peralatan_total'),
                              _l('peralatan_date'),
                              _l('peralatan_open_till'),
                              _l('tags'),
                              _l('peralatan_date_created'),
                              _l('peralatan_status'),
                            );

                             $custom_fields = get_custom_fields('peralatan',array('show_on_table'=>1));
                             foreach($custom_fields as $field){
                                array_push($table_data,$field['name']);
                             }

                             $table_data = hooks()->apply_filters('peralatan_table_columns', $table_data);
                             render_datatable($table_data,'peralatan',[],[
                                 'data-last-order-identifier' => 'peralatan',
                                 'data-default-order'         => get_table_last_order('peralatan'),
                             ]);
                           ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="peralatan" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [4,5,6,7];</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
   var peralatan_id;
   $(function(){
     var Peralatan_ServerParams = {};
     $.each($('._hidden_inputs._filters input'),function(){
       Peralatan_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });
     initDataTable('.table-peralatan', admin_url+'peralatan/small_table', ['undefined'], ['undefined'], Peralatan_ServerParams, [7, 'desc']);
     init_peralatan(peralatan_id);
   });
</script>
</body>
</html>
