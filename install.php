<?php

defined('BASEPATH') or exit('No direct script access allowed');


require_once('install/peralatan.php');
require_once('install/peralatan_activity.php');
require_once('install/peralatan_comments.php');

$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('peralatan', 'peralatan-send-to-client', 'english', 'Send peralatan to Customer', 'peralatan # {peralatan_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached peralatan <strong># {peralatan_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>peralatan status:</strong> {peralatan_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-already-send', 'english', 'peralatan Already Sent to Customer', 'peralatan # {peralatan_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your peralatan request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-declined-to-staff', 'english', 'peralatan Declined (Sent to Staff)', 'Customer Declined peralatan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined peralatan with number <strong># {peralatan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-accepted-to-staff', 'english', 'peralatan Accepted (Sent to Staff)', 'Customer Accepted peralatan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted peralatan with number <strong># {peralatan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting peralatan', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the peralatan.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-expiry-reminder', 'english', 'peralatan Expiration Reminder', 'peralatan Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The peralatan with <strong># {peralatan_number}</strong> will expire on <strong>{peralatan_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-send-to-client', 'english', 'Send peralatan to Customer', 'peralatan # {peralatan_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached peralatan <strong># {peralatan_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>peralatan status:</strong> {peralatan_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-already-send', 'english', 'peralatan Already Sent to Customer', 'peralatan # {peralatan_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your peralatan request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-declined-to-staff', 'english', 'peralatan Declined (Sent to Staff)', 'Customer Declined peralatan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined peralatan with number <strong># {peralatan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-accepted-to-staff', 'english', 'peralatan Accepted (Sent to Staff)', 'Customer Accepted peralatan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted peralatan with number <strong># {peralatan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'staff-added-as-project-member', 'english', 'Staff Added as Project Member', 'New project assigned to you', '<p>Hi <br /><br />New peralatan has been assigned to you.<br /><br />You can view the peralatan on the following link <a href=\"{peralatan_link}\">peralatan__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('peralatan', 'peralatan-accepted-to-staff', 'english', 'peralatan Accepted (Sent to Staff)', 'Customer Accepted peralatan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted peralatan with number <strong># {peralatan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the peralatan on the following link: <a href=\"{peralatan_link}\">{peralatan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for peralatan
add_option('delete_only_on_last_peralatan', 1);
add_option('peralatan_prefix', 'SCH-');
add_option('next_peralatan_number', 1);
add_option('default_peralatan_assigned', 9);
add_option('peralatan_number_decrement_on_delete', 0);
add_option('peralatan_number_format', 4);
add_option('peralatan_year', date('Y'));
add_option('exclude_peralatan_from_client_area_with_draft_status', 1);
add_option('predefined_client_note_peralatan', '- Staf diatas untuk melakukan riksa uji pada peralatan tersebut.
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');
add_option('predefined_terms_peralatan', '- Pelaksanaan riksa uji harus mengikuti prosedur yang ditetapkan peralatan pemilik alat.
- Dilarang membuat dokumentasi tanpa seizin peralatan pemilik alat.
- Dokumen ini diterbitkan dari sistem CRM, tidak memerlukan tanda tangan dari PT. Cipta Mas Jaya');
add_option('peralatan_due_after', 1);
add_option('allow_staff_view_peralatan_assigned', 1);
add_option('show_assigned_on_peralatan', 1);
add_option('require_client_logged_in_to_view_peralatan', 0);

add_option('show_project_on_peralatan', 1);
add_option('peralatan_pipeline_limit', 1);
add_option('default_peralatan_pipeline_sort', 1);
add_option('peralatan_accept_identity_confirmation', 1);
add_option('peralatan_qrcode_size', '160');
add_option('peralatan_send_telegram_message', 0);


/*

DROP TABLE `tblperalatan`;
DROP TABLE `tblperalatan_activity`, `tblperalatan_items`, `tblperalatan_members`;
delete FROM `tbloptions` WHERE `name` LIKE '%peralatan%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'peralatan';



*/