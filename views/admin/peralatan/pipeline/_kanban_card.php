<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($peralatan['status'] == $status) { ?>
<li data-peralatan-id="<?php echo $peralatan['id']; ?>" class="<?php if($peralatan['invoice_id'] != NULL || $peralatan['peralatan_id'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading">
               <a href="<?php echo admin_url('peralatan/list_peralatan/'.$peralatan['id']); ?>" data-toggle="tooltip" data-title="<?php echo $peralatan['subject']; ?>" onclick="peralatan_pipeline_open(<?php echo $peralatan['id']; ?>); return false;"><?php echo format_peralatan_number($peralatan['id']); ?></a>
               <?php if(has_permission('peralatan','','edit')){ ?>
               <a href="<?php echo admin_url('peralatan/peralatan/'.$peralatan['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="mbot10 inline-block full-width">
            <?php
               if($peralatan['rel_type'] == 'lead'){
                 echo '<a href="'.admin_url('leads/index/'.$peralatan['clientid']).'" onclick="init_lead('.$peralatan['clientid'].'); return false;" data-toggle="tooltip" data-title="'._l('lead').'">' .$peralatan['peralatan_to'].'</a><br />';
               } else if($peralatan['rel_type'] == 'customer'){
                 echo '<a href="'.admin_url('clients/client/'.$peralatan['clientid']).'" data-toggle="tooltip" data-title="'._l('client').'">' .$peralatan['peralatan_to'].'</a><br />';
               }
               ?>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <?php if($peralatan['total'] != 0){
                     ?>
                  <span class="bold"><?php echo _l('peralatan_total'); ?>:
                     <?php echo app_format_money($peralatan['total'], get_currency($peralatan['currency'])); ?>
                  </span>
                  <br />
                  <?php } ?>
                  <?php echo _l('peralatan_date'); ?>: <?php echo _d($peralatan['date']); ?>
                  <?php if(is_date($peralatan['open_till'])){ ?>
                  <br />
                  <?php echo _l('peralatan_open_till'); ?>: <?php echo _d($peralatan['open_till']); ?>
                  <?php } ?>
                  <br />
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-comments" aria-hidden="true"></i> <?php echo _l('peralatan_comments'); ?>: <?php echo total_rows(db_prefix().'peralatan_comments', array(
                     'peralatan_id' => $peralatan['id']
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($peralatan['id'],'peralatan');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
