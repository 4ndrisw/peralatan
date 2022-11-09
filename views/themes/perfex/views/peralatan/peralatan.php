<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-peralatan">
  <div class="panel-body">
    <h4 class="no-margin section-text"><?php echo _l('peralatan'); ?></h4>
  </div>
</div>
<div class="panel_s">
  <div class="panel-body">
    <table class="table dt-table table-peralatan" data-order-col="3" data-order-type="desc">
      <thead>
        <tr>
          <th class="th-peralatan-number"><?php echo _l('peralatan') . ' #'; ?></th>
          <th class="th-peralatan-subject"><?php echo _l('peralatan_subject'); ?></th>
          <th class="th-peralatan-total"><?php echo _l('peralatan_total'); ?></th>
          <th class="th-peralatan-open-till"><?php echo _l('peralatan_open_till'); ?></th>
          <th class="th-peralatan-date"><?php echo _l('peralatan_date'); ?></th>
          <th class="th-peralatan-status"><?php echo _l('peralatan_status'); ?></th>
          <?php
          $custom_fields = get_custom_fields('peralatan',array('show_on_client_portal'=>1));
          foreach($custom_fields as $field){ ?>
            <th><?php echo $field['name']; ?></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($daftar_peralatan as $peralatan){ ?>
          <tr>
            <td>
              <a href="<?php echo site_url('peralatan/'.$peralatan['id'].'/'.$peralatan['hash']); ?>" class="td-peralatan-url">
                <?php echo format_peralatan_number($peralatan['id']); ?>
                <?php
                if ($peralatan['invoice_id']) {
                  echo '<br /><span class="text-success peralatan-invoiced">' . _l('peralatan_invoiced') . '</span>';
                }
                ?>
              </a>
              <td>
                <a href="<?php echo site_url('peralatan/'.$peralatan['id'].'/'.$peralatan['hash']); ?>" class="td-peralatan-url-subject">
                  <?php echo $peralatan['subject']; ?>
                </a>
                <?php
                if ($peralatan['invoice_id'] != NULL) {
                  $invoice = $this->invoices_model->get($peralatan['invoice_id']);
                  echo '<br /><a href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '" target="_blank" class="td-peralatan-invoice-url">' . format_invoice_number($invoice->id) . '</a>';
                } else if ($peralatan['peralatan_id'] != NULL) {
                  $peralatan = $this->peralatan_model->get($peralatan['peralatan_id']);
                  echo '<br /><a href="' . site_url('peralatan/' . $peralatan->id . '/' . $peralatan->hash) . '" target="_blank" class="td-peralatan-peralatan-url">' . format_peralatan_number($peralatan->id) . '</a>';
                }
                ?>
              </td>
              <td data-order="<?php echo $peralatan['total']; ?>">
                <?php
                if ($peralatan['currency'] != 0) {
                 echo app_format_money($peralatan['total'], get_currency($peralatan['currency']));
               } else {
                 echo app_format_money($peralatan['total'], get_base_currency());
               }
               ?>
             </td>
             <td data-order="<?php echo $peralatan['open_till']; ?>"><?php echo _d($peralatan['open_till']); ?></td>
             <td data-order="<?php echo $peralatan['date']; ?>"><?php echo _d($peralatan['date']); ?></td>
             <td><?php echo format_peralatan_status($peralatan['status']); ?></td>
             <?php foreach($custom_fields as $field){ ?>
               <td><?php echo get_custom_field_value($peralatan['id'],$field['id'],'peralatan'); ?></td>
             <?php } ?>
           </tr>
         <?php } ?>
       </tbody>
     </table>
   </div>
 </div>
