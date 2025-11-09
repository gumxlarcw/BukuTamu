<?php
$ci =& get_instance();
$ci->load->database();
echo "HOST: " . $ci->db->hostname . "<br>";
echo "DB: " . $ci->db->database . "<br>";
