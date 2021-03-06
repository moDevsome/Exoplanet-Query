<?php

function moving($src, $dest) : bool {

    if(rename($src, $dest) === FALSE) {

        echo "\033[31m => FAILED\033[37m\r\n";
        return FALSE;

    }
    else { return TRUE; }

}

function delete_dir(string $folder_path) : bool {

    foreach(array_diff(scandir($folder_path), ['.', '..']) as $path) {


        if(is_file($folder_path.$path)) {

            if(unlink($folder_path.$path) === FALSE) {

                break;
                return FALSE;

            }

        }
        else {

            if(delete_dir($folder_path.$path.DIRECTORY_SEPARATOR) === FALSE) {

                break;
                return FALSE;

            }
            else {

                if(rmdir($folder_path.$path.DIRECTORY_SEPARATOR) === FALSE) {

                    break;
                    return FALSE;

                }

            }

        }

    }

    return TRUE;

}

define('ROOT_DIR', dirname(__FILE__));
echo "Getting Frontend Dependencies\r\n";
echo "\r\n";

$dependencies = json_decode(file_get_contents(ROOT_DIR.DIRECTORY_SEPARATOR.'dependencies.json'), TRUE);
$node_directory = ROOT_DIR.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR;
$has_error = FALSE;

$bootstrap_css_directory = $node_directory.'bootstrap'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR;
if(!is_dir($node_directory)) {

    echo "\033[31mERROR\033[37m\r\n";
    echo "The directory ".$node_directory." does not exist\r\n";
    echo "Please execute `npm install`\r\n";
    die;

}
else {

    mkdir(ROOT_DIR.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR);

    $assets_bootstrap_css_dir = ROOT_DIR.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR;
    mkdir($assets_bootstrap_css_dir);
    foreach($dependencies['bootstrap_css'] as $bootstrap_css_file) {

        if(file_exists($bootstrap_css_directory.$bootstrap_css_file)) {

            if(moving($bootstrap_css_directory.$bootstrap_css_file, $assets_bootstrap_css_dir.$bootstrap_css_file) === FALSE) $has_error = TRUE;
        }

    }

}


$bootstrap_ico_directory = $node_directory.'bootstrap-icons'.DIRECTORY_SEPARATOR.'font'.DIRECTORY_SEPARATOR;
$assets_bootstrap_icon_dir = ROOT_DIR.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'icons-1.6.1'.DIRECTORY_SEPARATOR;
mkdir($assets_bootstrap_icon_dir);
mkdir($assets_bootstrap_icon_dir.'fonts'.DIRECTORY_SEPARATOR);
foreach($dependencies['bootstrap_ico'] as $bootstrap_ico_file) {

    $bootstrap_ico_file = str_ireplace('__DS__', DIRECTORY_SEPARATOR, $bootstrap_ico_file);
    if(file_exists($bootstrap_ico_directory.$bootstrap_ico_file)) {

        if(moving($bootstrap_ico_directory.$bootstrap_ico_file, $assets_bootstrap_icon_dir.$bootstrap_ico_file) === FALSE) $has_error = TRUE;

    }

}

if(delete_dir($node_directory) === FALSE) {

    echo "\033[31mThe directory : ".$node_directory." has been not deleted.\033[37m\r\n";

}
else {

    if(rmdir($node_directory) === FALSE) {

        echo "\033[31mThe directory : ".$node_directory." has been not deleted.\033[37m\r\n";

    }
    else {

        echo "\033[32mThe directory : ".$node_directory." has been well deleted.\033[37m\r\n";

    }

}

$env_str = file_get_contents(ROOT_DIR.DIRECTORY_SEPARATOR.'.env');
$env_str.= PHP_EOL;
$env_str.= '### Added by "deploy-dev.php". Define a custom redirect base if it not sent by the server  ###'.PHP_EOL;
$env_str.= 'APP_REDIRECT_BASE=';
file_put_contents(ROOT_DIR.DIRECTORY_SEPARATOR.'.env', $env_str);

rename(ROOT_DIR.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'htaccess.txt', ROOT_DIR.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'.htaccess');

$app_files_folder = ROOT_DIR.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'app_files';
mkdir($app_files_folder);
echo "\r\n";
echo "\033[33mIMPORTANT, Last step !\033[37m\r\n";
echo "\r\n";
echo "You need to add the file \033[34m".$app_files_folder.DIRECTORY_SEPARATOR."DB.csv\033[37m, please see the github project page to know how to import this file.\r\n";
