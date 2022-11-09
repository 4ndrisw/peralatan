<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="stats-top" class="hide">
    <div id="peralatan_total"></div>
    <div class="panel_s">
        <div class="panel-body">
         <div class="_filters _hidden_inputs hidden">
            <?php
            if(isset($peralatan_sale_agents)){
                foreach($peralatan_sale_agents as $agent){
                    echo form_hidden('sale_agent_'.$agent['sale_agent']);
                }
            }
            if(isset($peralatan_statuses)){
                foreach($peralatan_statuses as $_status){
                    $val = '';
                    if($_status == $this->input->get('status')){
                        $val = $_status;
                    }
                    echo form_hidden('peralatan_'.$_status,$val);
                }
            }
            if(isset($peralatan_years)){
                foreach($peralatan_years as $year){
                    echo form_hidden('year_'.$year['year'],$year['year']);
                }
            }
            echo form_hidden('not_sent',$this->input->get('filter'));
            echo form_hidden('project_id');
            echo form_hidden('invoiced');
            echo form_hidden('not_invoiced');
            ?>
        </div>
        <div class="row text-left quick-top-stats">
            <?php foreach($peralatan_statuses as $status){
              $percent_data = get_peralatan_percent_by_status($status, (isset($project) ? $project->id : null));
              ?>
              <div class="col-md-5ths col-xs-12">
                <div class="row">
                    <div class="col-md-7">
                        <a href="#" data-cview="peralatan_<?php echo $status; ?>" onclick="dt_custom_view('peralatan_<?php echo $status; ?>','.table-peralatan','peralatan_<?php echo $status; ?>',true); return false;">
                            <h5><?php echo format_peralatan_status($status,'',false); ?></h5>
                        </a>
                    </div>
                    <div class="col-md-5 text-right">
                        <?php echo $percent_data['total_by_status']; ?> / <?php echo $percent_data['total']; ?>
                    </div>
                    <div class="col-md-12">
                        <div class="progress no-margin">
                            <div class="progress-bar progress-bar-<?php echo peralatan_status_color_class($status); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<hr />
</div>
