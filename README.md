# Change Images URL
This plugin changes the images listed for a new inserted image and delete the old images;

Author: Matheus Aguiar
Author URL: https://github.com/aguiarr
Author Email: aguiartgv@gmail.com
Contributors: aguiarr  
Tested up to: 1.0.0  
Stable tag: 1.0.0  

## How to use:

1. Insert the new image ID on the field "New Image ID"
2. Insert an URL list (Ex: 2020/12/test-image.png)separated by ";" on the field "Image List";

## Observation

In the last 2 fields will be printed the URLs that were successful and those that failed and in which part of the operation it failed.

There will always be one or other that fails in the update because not all images in the database have the meta_key "_thumbnail_id", if you do not go through the update the url will not be deleted;

## Installation

Open menu Settings -> Change Image URL

