<?php
# $Id: horizthinbarline2.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test - thinbarline, horiz & vert - data-data vertical <0 & >0
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'implicit' => True,       # If true, use text-data-*
  'low' => -5,              # Bottom of data range
  'high' => 5,              # Top of data range
  );
require 'horizthinbarline.php';
