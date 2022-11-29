<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$peralatan->id); ?>
<?php echo form_hidden('_attachment_sale_type','peralatan'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_peralatan" aria-controls="tab_peralatan" role="tab" data-toggle="tab">
                  <?php echo _l('peralatan'); ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                  <?php echo _l('peralatan_view_activity_tooltip'); ?>
                  </a>
               </li>
               <?php if(isset($peralatan)){ ?>
               <li role="presentation">
                  <a href="#tab_comments" onclick="get_peralatan_comments(); return false;" aria-controls="tab_comments" role="tab" data-toggle="tab">
                  <?php
                  echo _l('peralatan_comments');
                  $total_comments = total_rows(db_prefix() . 'peralatan_comments', [
                      'peralatan_id' => $peralatan->id,
                    ]
                  );
                  ?>
                      <span class="badge total_comments <?php echo $total_comments === 0 ? 'hide' : ''; ?>"><?php echo $total_comments ?></span>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $peralatan->id ;?> + '/' + 'peralatan', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                  <?php echo _l('peralatan_reminders'); ?>
                  <?php
                     $total_reminders = total_rows(db_prefix().'reminders',
                      array(
                       'isnotified'=>0,
                       'staff'=>get_staff_user_id(),
                       'rel_type'=>'peralatan',
                       'rel_id'=>$peralatan->id
                       )
                      );
                     if($total_reminders > 0){
                      echo '<span class="badge">'.$total_reminders.'</span>';
                     }
                     ?>
                  </a>
               </li>
               <li role="presentation" class="tab-separator">
                  <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $peralatan->id; ?>,'peralatan'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                  <?php echo _l('tasks'); ?>
                  </a>
               </li>
               <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $peralatan->id; ?>,'peralatan'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('peralatan_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
               </li>
               <li role="presentation" class="tab-separator">
                     <a href="#tab_templates" onclick="get_templates('peralatan', <?php echo $peralatan->id ?? '' ?>); return false" aria-controls="tab_templates" role="tab" data-toggle="tab">
                        <?php
                        echo _l('templates');
                        $total_templates = total_rows(db_prefix() . 'templates', [
                            'type' => 'peralatan',
                          ]
                        );
                        ?>
                         <span class="badge total_templates <?php echo $total_templates === 0 ? 'hide' : ''; ?>"><?php echo $total_templates ?></span>
                     </a>
               </li>
               <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                  <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                  <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                  <a href="#" onclick="small_table_full_view(); return false;">
                  <i class="fa fa-expand"></i></a>
               </li>
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="row mtop10">
         <div class="col-md-3">
            <?php echo format_peralatan_status($peralatan->status,'pull-left mright5 mtop5'); ?>
         </div>
         <div class="col-md-9 text-right _buttons peralatan_buttons">
            <?php if(has_permission('peralatan','','edit')){ ?>
            <a href="<?php echo admin_url('peralatan/edit_peralatan/'.$peralatan->id); ?>" data-placement="left" data-toggle="tooltip" title="<?php echo _l('peralatan_edit'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa-regular fa-pen-to-square"></i></a>
            <?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo site_url('peralatan/pdf/'.$peralatan->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo site_url('peralatan/pdf/'.$peralatan->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li><a href="<?php echo site_url('peralatan/pdf/'.$peralatan->id); ?>"><?php echo _l('download'); ?></a></li>
                  <li>
                     <a href="<?php echo site_url('peralatan/pdf/'.$peralatan->id.'?print=true'); ?>" target="_blank">
                     <?php echo _l('print'); ?>
                     </a>
                  </li>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#peralatan_send_to_customer" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?php echo _l('peralatan_send_to_email'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="<?php echo site_url('peralatan/'.$peralatan->id .'/'.$peralatan->hash); ?>" target="_blank"><?php echo _l('peralatan_view'); ?></a>
                  </li>
                  <?php hooks()->do_action('after_peralatan_view_as_client_link', $peralatan); ?>
                  <?php if(!empty($peralatan->open_till) && date('Y-m-d') < $peralatan->open_till && ($peralatan->status == 4 || $peralatan->status == 1) && is_peralatan_expiry_reminders_enabled()) { ?>
                  <li>
                     <a href="<?php echo admin_url('peralatan/send_expiry_reminder/'.$peralatan->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                  </li>
                  <?php } ?>
                  <li>
                     <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if(has_permission('peralatan','','create')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'peralatan/copy/'.$peralatan->id; ?>"><?php echo _l('peralatan_copy'); ?></a>
                  </li>
                  <?php } ?>
                  <?php if($peralatan->status != '6'){ ?>
                  <?php foreach($peralatan_statuses as $status){
                     if(has_permission('peralatan','','edit')){
                      if($peralatan->status != $status){ ?>
                           <li>
                              <a href="<?php echo admin_url() . 'peralatan/mark_action_status/'.$status.'/'.$peralatan->id; ?>"><?php echo _l('peralatan_mark_as',format_peralatan_status($status,'',false)); ?></a>
                           </li>
                  <?php
                     } } } ?>
                  <?php } ?>
                  <?php if(!empty($peralatan->signature) && has_permission('peralatan','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url('peralatan/clear_signature/'.$peralatan->id); ?>" class="_delete">
                     <?php echo _l('clear_signature'); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php if(has_permission('peralatan','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'peralatan/delete/'.$peralatan->id; ?>" class="text-danger delete-text _delete"><?php echo _l('peralatan_delete'); ?></a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if($peralatan->id == NULL && $peralatan->invoice_id == NULL){ ?>
            <?php if(has_permission('peralatan','','create') || has_permission('invoices','','create')){ ?>
            <div class="btn-group">
               <button type="button" class="btn btn-success dropdown-toggle<?php if($peralatan->rel_type == 'customer' && total_rows(db_prefix().'clients',array('active'=>0,'userid'=>$peralatan->clientid)) > 0){echo ' disabled';} ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('peralatan_convert'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <?php
                     $disable_convert = false;
                     $not_related = false;

                     if($peralatan->rel_type == 'lead'){
                      if(total_rows(db_prefix().'clients',array('leadid'=>$peralatan->clientid)) == 0){
                       $disable_convert = true;
                       $help_text = 'peralatan_convert_to_lead_disabled_help';
                     }
                     } else if(empty($peralatan->rel_type)){
                     $disable_convert = true;
                     $help_text = 'peralatan_convert_not_related_help';
                     }
                     ?>
                  <?php if(has_permission('peralatan','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('peralatan_convert_peralatan')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="peralatan" onclick="peralatan_convert_template(this); return false;"';} ?>><?php echo _l('peralatan_convert_peralatan'); ?></a></li>
                  <?php } ?>
                  <?php if(has_permission('invoices','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('peralatan_convert_invoice')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="invoice" onclick="peralatan_convert_template(this); return false;"';} ?>><?php echo _l('peralatan_convert_invoice'); ?></a></li>
                  <?php } ?>
               </ul>
            </div>
            <?php } ?>
            <?php } else {
               if($peralatan->id != NULL){
                echo '<a href="'.admin_url('peralatan/list_peralatan/'.$peralatan->id . '#'.$peralatan->id).'" class="btn btn-info">'.format_peralatan_number($peralatan->id).'</a>';
               } else {
                echo '<a href="'.admin_url('invoices/list_invoices/'.$peralatan->invoice_id).'" class="btn btn-info">'.format_invoice_number($peralatan->invoice_id).'</a>';
               }
               } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_peralatan">
                  <div class="row mtop10">
                     <?php if($peralatan->status == 6 && !empty($peralatan->acceptance_firstname) && !empty($peralatan->acceptance_lastname) && !empty($peralatan->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info">
                           <?php echo _l('accepted_identity_info',array(
                              _l('peralatan_lowercase'),
                              '<b>'.$peralatan->acceptance_firstname . ' ' . $peralatan->acceptance_lastname . '</b> (<a href="mailto:'.$peralatan->acceptance_email.'">'.$peralatan->acceptance_email.'</a>)',
                              '<b>'. _dt($peralatan->acceptance_date).'</b>',
                              '<b>'.$peralatan->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('peralatan/clear_acceptance_info/'.$peralatan->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <div class="col-md-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($peralatan->id,'peralatan');
                              if(count($tags) > 0){
                               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('peralatan/list_peralatan/'.$peralatan->id . '#'.$peralatan->id); ?>">
                           <span id="peralatan-number">
                           <?php echo format_peralatan_number($peralatan->id); ?>
                           </span>
                           </a>
                        </h4>
                        <h5 class="bold mbot15 font-medium"><a href="<?php echo admin_url('peralatan/list_peralatan/'.$peralatan->id . '#'.$peralatan->id); ?>"><?php echo $peralatan->subject; ?></a></h5>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-md-6 text-right">
                        <address>
                           <span class="bold"><?php echo _l('peralatan_to'); ?>:</span><br />
                           <?php echo format_peralatan_info($peralatan,'admin'); ?>
                        </address>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <?php
                     if(count($peralatan->attachments) > 0){ ?>
                  <p class="bold"><?php echo _l('peralatan_files'); ?></p>
                  <?php foreach($peralatan->attachments as $attachment){
                     $attachment_url = site_url('download/file/peralatan/'.$attachment['attachment_key']);
                     if(!empty($attachment['external'])){
                        $attachment_url = $attachment['external_link'];
                     }
                     ?>
                  <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
                     <div class="col-md-8">
                        <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                        <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                        <br />
                        <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                     </div>
                     <div class="col-md-4 text-right">
                        <?php if($attachment['visible_to_customer'] == 0){
                           $icon = 'fa-toggle-off';
                           $tooltip = _l('show_to_customer');
                           } else {
                           $icon = 'fa-toggle-on';
                           $tooltip = _l('hide_from_customer');
                           }
                           ?>
                        <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $peralatan->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_peralatan_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                  <div class="clearfix"></div>

                  <div class="row">
                     
                     <?php if(count($peralatan->attachments) > 0){ ?>
                     <div class="clearfix"></div>
                     <hr />
                     <div class="col-md-12">
                        <p class="bold text-muted"><?php echo _l('peralatan_files'); ?></p>
                     </div>
                     <?php foreach($peralatan->attachments as $attachment){
                        $attachment_url = site_url('download/file/peralatan/'.$attachment['attachment_key']);
                        if(!empty($attachment['external'])){
                          $attachment_url = $attachment['external_link'];
                        }
                        ?>
                     <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                        <div class="col-md-8">
                           <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                           <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                           <br />
                           <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                        </div>
                        <div class="col-md-4 text-right">
                           <?php if($attachment['visible_to_customer'] == 0){
                              $icon = 'fa fa-toggle-off';
                              $tooltip = _l('show_to_customer');
                              } else {
                              $icon = 'fa fa-toggle-on';
                              $tooltip = _l('hide_from_customer');
                              }
                              ?>
                           <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $peralatan->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                           <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                           <a href="#" class="text-danger" onclick="delete_peralatan_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                           <?php } ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php } ?>
                     
                  </div>

                      <?php if(!empty($peralatan->signature)) { ?>
                        <div class="row mtop25">
                           <div class="col-md-6 col-md-offset-6 text-right">
                              <div class="bold">
                                 <p class="no-mbot"><?php echo _l('contract_signed_by') . ": {$peralatan->acceptance_firstname} {$peralatan->acceptance_lastname}"?></p>
                                 <p class="no-mbot"><?php echo _l('peralatan_signed_date') . ': ' . _dt($peralatan->acceptance_date) ?></p>
                                 <p class="no-mbot"><?php echo _l('peralatan_signed_ip') . ": {$peralatan->acceptance_ip}"?></p>
                              </div>
                              <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                                 <?php if(has_permission('peralatan','','delete')){ ?>
                                 <a href="<?php echo admin_url('peralatan/clear_signature/'.$peralatan->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                                 </a>
                                 <?php } ?>
                              </p>
                              <div class="pull-right">
                                 <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_upload_path_by_type('peralatan').$peralatan->id.'/'.$peralatan->signature)); ?>" class="img-responsive" alt="">
                              </div>
                           </div>
                        </div>
                        <?php } ?>
               </div>

               <div role="tabpanel" class="tab-pane" id="tab_activity">
                  <div class="row">
                     <div class="col-md-12">
                        <div class="activity-feed">
                           <?php foreach($activity as $activity){
                              $_custom_data = false;
                              ?>
                           <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                              <div class="date">
                                 <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                                 <?php echo time_ago($activity['date']); ?>
                                 </span>
                              </div>
                              <div class="text">
                                 <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                                 <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                                 <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                    ?>
                                 </a>
                                 <?php } ?>
                                 <?php
                                    $additional_data = '';
                                    if(!empty($activity['additional_data'])){
                                     $additional_data = unserialize($activity['additional_data']);

                                     $i = 0;
                                     foreach($additional_data as $data){
                                       if(strpos($data,'<original_status>') !== false){
                                         $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                         $additional_data[$i] = format_peralatan_status($original_status,'',false);
                                       } else if(strpos($data,'<new_status>') !== false){
                                         $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                         $additional_data[$i] = format_peralatan_status($new_status,'',false);
                                       } else if(strpos($data,'<status>') !== false){
                                         $status = get_string_between($data, '<status>', '</status>');
                                         $additional_data[$i] = format_peralatan_status($status,'',false);
                                       } else if(strpos($data,'<custom_data>') !== false){
                                         $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                         unset($additional_data[$i]);
                                       }
                                       $i++;
                                     }
                                    }
                                    $_formatted_activity = _l($activity['description'],$additional_data);
                                    if($_custom_data !== false){
                                    $_formatted_activity .= ' - ' .$_custom_data;
                                    }
                                    if(!empty($activity['full_name'])){
                                    $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                    }
                                    echo $_formatted_activity;
                                    if(is_admin()){
                                    echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                    }
                                    ?>
                              </div>
                           </div>
                           <?php } ?>
                        </div>
                     </div>
                  </div>
               </div>

               <div role="tabpanel" class="tab-pane" id="tab_comments">
                  <div class="row peralatan-comments mtop15">
                     <div class="col-md-12">
                        <div id="peralatan-comments"></div>
                        <div class="clearfix"></div>
                        <textarea name="content" id="comment" rows="4" class="form-control mtop15 peralatan-comment"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_peralatan_comment();"><?php echo _l('peralatan_add_comment'); ?></button>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_notes">
                  <?php echo form_open(admin_url('peralatan/add_note/'.$peralatan->id),array('id'=>'sales-notes','class'=>'peralatan-notes-form')); ?>
                  <?php echo render_textarea('description'); ?>
                  <div class="text-right">
                     <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('peralatan_add_note'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
                  <hr />
                  <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_templates">
                  <div class="row peralatan-templates">
                     <div class="col-md-12">
                        <button type="button" class="btn btn-info" onclick="add_template('peralatan',<?php echo $peralatan->id ?? '' ?>);"><?php echo _l('add_template'); ?></button>
                        <hr>
                     </div>
                     <div class="col-md-12">
                        <div id="peralatan-templates" class="peralatan-templates-wrapper"></div>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                  <?php
                     $this->load->view('admin/includes/emails_tracking',array(
                       'tracked_emails'=>
                       get_tracked_emails($peralatan->id, 'peralatan'))
                       );
                     ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tasks">
                  <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$peralatan->id,'data-new-rel-type'=>'peralatan')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-peralatan-<?php echo $peralatan->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('peralatan_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$peralatan->id,'name'=>'peralatan','members'=>$members,'reminder_title'=>_l('peralatan_set_reminder_title'))); ?>
               </div>
               <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                  <?php
                     $views_activity = get_views_tracking('peralatan',$peralatan->id);
                       if(count($views_activity) === 0) {
                     echo '<h4 class="no-margin">'._l('not_viewed_yet',_l('peralatan_lowercase')).'</h4>';
                     }
                     foreach($views_activity as $activity){ ?>
                  <p class="text-success no-margin">
                     <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                  </p>
                  <p class="text-muted">
                     <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                  </p>
                  <hr />
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="modal-wrapper"></div>
<?php //$this->load->view('admin/peralatan/send_peralatan_to_email_template'); ?>
<script>
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
     // defined in manage peralatan
     peralatan_id = '<?php echo $peralatan->id; ?>';
     //init_peralatan_editor();
</script>
