# izi
Single-script file sharing system in PHP 

Current version: `4.6` | `2022-06-20`  
License: `AGPLv3`

## About
**izi** allows you to very easily set up an interface on your server to easily upload, share and manage files.

See screenshots of the interface [here](https://github.com/aaviator42/izi/tree/main/screenshots).

## Installation
1. Save `izi.php` on your server. You can rename it if you want (for e.g., to  `index.php`).
2. Create a directory where your files will be uploaded. Make sure it is publicly accessibly.  
3. Configure the options at the top of `izi.php`.  
4. All done!  

### Recommended folder structure:
```
  +-[parent folder]
   |-izi.php
   |-files/
 ``` 

## Requirements
1. [Supported versions of PHP](https://www.php.net/supported-versions.php). At the time of writing, that's PHP `7.4+`. izi will almost certainly work on older versions, but we don't test it on those, so be careful, do your own testing.
 
## Misc. Considerations
1. You might want to enforce TLS through your server's configuration, because the setting in `izi.php` will only enforce it for the interface itself and won't (can't) enforce it for the files you share. 
2. You also might want to leave an empty `index.html` file in the directory where your files are stored, or disable directory listing.
