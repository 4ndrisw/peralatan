<?php defined('BASEPATH') or exit('No direct script access allowed');
$i = 0;
foreach ($statuses as $status) {
  $kanBan = new modules\peralatan\services\peralatan\PeralatanPipeline($status);
  $kanBan->search($this->input->get('search'))
    ->sortBy($this->input->get('sort_by'),$this->input->get('sort'));
    if($this->input->get('refresh')) {
        $kanBan->refresh($this->input->get('refresh')[$status] ?? null);
    }
  $daftar_peralatan = $kanBan->get();
  $total_peralatan = count($daftar_peralatan);
  $total_pages = $kanBan->totalPages();
  ?>
 <ul class="kan-ban-col" data-col-status-id="<?php echo $status; ?>" data-total-pages="<?php echo $total_pages; ?>" data-total="<?php echo $total_peralatan; ?>">
  <li class="kan-ban-col-wrapper">
    <div class="border-right panel_s no-mbot">
      <div class="panel-heading-bg <?php echo peralatan_status_color_class($status); ?>-bg">
       <div class="kan-ban-step-indicator<?php if($i == count($statuses) -1){ echo ' kan-ban-step-indicator-full'; } ?>"></div>
       <?php echo format_peralatan_status($status,'',false); ?> - <?php echo $kanBan->countAll() . ' ' . _l('peralatan') ?>
     </div>
     <div class="kan-ban-content-wrapper">
      <div class="kan-ban-content">
        <ul class="sortable<?php if(has_permission('peralatan','','edit')){echo ' status pipeline-status'; } ?>" data-status-id="<?php echo $status; ?>">
          <?php
          foreach ($daftar_peralatan as $peralatan) {
              $this->load->view('admin/peralatan/pipeline/_kanban_card',array('peralatan'=>$peralatan,'status'=>$status));
          }
          ?>
          <?php if($total_peralatan > 0 ){ ?>
          <li class="text-center not-sortable kanban-load-more" data-load-status="<?php echo $status; ?>">
            <a href="#" class="btn btn-default btn-block<?php if($total_pages <= 1 || $kanBan->getPage() === $total_pages){echo ' disabled';} ?>" data-page="<?php echo $kanBan->getPage(); ?>" onclick="kanban_load_more(<?php echo $status; ?>,this,'peralatan/pipeline_load_more',347,360); return false;";><?php echo _l('load_more'); ?></a>
          </li>
          <?php } ?>
          <li class="text-center not-sortable mtop30 kanban-empty<?php if($total_peralatan > 0){echo ' hide';} ?>">
            <h4>
              <i class="fa fa-circle-o-notch" aria-hidden="true"></i><br /><br />
              <?php echo _l('no_peralatan_found'); ?></h4>
            </li>
          </ul>
        </div>
      </div>
    </li>
  </ul>
  <?php $i++;} ?>
