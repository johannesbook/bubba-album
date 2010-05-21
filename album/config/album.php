<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['image_path'] = '';
$config['thumbs_path'] = '/var/lib/album/thumbs';

# number of images per album page
$config['image_chunks'] = 120;

# 4:3 form factor
$config['thumb_width'] = 100;
$config['thumb_height'] = 100;

# number of images in a row of subalbums
$config['album_width'] = 4;

# number of columns in thumbs preview
$config['thumbs_col'] = 2;

# width of rescaled image, will retain aspect, and wont be cropped
$config['rescaled_width'] = 600;

