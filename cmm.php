<?php
print "--------------------------------------------------------------------------------------------------------------------------------------------------------\n";
print "Scanning for all mkv, avi & mp4 files.\n";
// Get a list of files in the current dir
$files = glob('*.{mp4,mkv,avi}', GLOB_BRACE);
// loop through each file and check to see if any spaces are in the filenames, if so replace with periods
print "Found " . count($files) . " files.\n";
print "--------------------------------------------------------------------------------------------------------------------------------------------------------\n";
foreach ($files as $file){
    $orig_filename=$file;
    $new_filename=str_replace(" ", ".", $file);
    $new_filename=preg_replace("/[^a-zA-Z0-9\.\-]/", "", $new_filename);
    if (strcmp($orig_filename, $new_filename) !== 0) {
        print "Filenames are different, renaming.\n";
        rename ($orig_filename, $new_filename);
    }
    $renamed = renameIt($new_filename);
    $info = getInfo($new_filename);
//    print_r($info);
    if ($info[af]=="MPEG Audio"){$audio="aac";} else {$audio="copy";}
    if (($info[vf]=="HEVC")||($info[vf]=="x265")){ print "HEVC File found. Skipping.\n"; moveit($new_filename, $new_filename, "Skip"); continue; }
    $video="hevc_nvenc";
    $avbr=round($info[obr] / 2);
    $maxbr=$avbr + 500000;
    print $info[name] . " Will be converted to HEVC with " . $audio . " audio.\n";
    print "Bitrates will be Average - " . $avbr . " With an absolute maximum of " . $maxbr . "\n";
    if (($info[width]>=1280) && ($info[width]<=1919)){$res=".720p";}
    if (($info[width]>=1920) && ($info[width]<=3839)){$res=".1080p";}
    if ($info[width]>=3840) {$res=".UHD";}
    if ($info[width]<=1279) {$res="";}
    if ($info[af]=="AC-3") {$af=".AC3";}
    if ($info[af]=="AAC") {$af=".AAC";}
    if ($audio="aac"){$af=".AAC";}
    $output=$renamed . $res . ".HEVC" . $af . "-CmM.mkv";
    print $output . " Will be the new filename.\n";
    convertit($new_filename, $output, $audio, $avbr, $maxbr);
    moveit($new_filename, $output, "Completed");
    print "--------------------------------------------------------------------------------------------------------------------------------------------------------\n";
}


function renameIt($old){
    $tags = array(".1080p", ".720p", ".UHD", ".4k", ".h264", ".x264", ".XviD", ".DivX", ".mp4", ".mkv", ".avi");
    foreach ($tags as $tag){
        if (stripos($old, $tag) !== false ){
            $newname = explode($tag, $old)[0];
            return($newname);;
        } else {
            continue;
        }
    }
}

function getInfo($media){
    exec("mediainfo --Output=file://tpl.ini " . $media, $result);
    $fileinfo = explode(",", $result[0]);
    $finfo = array(
        "obr" => $fileinfo[0],
        "vf" => $fileinfo[7],
        "af" => $fileinfo[9],
        "width" => $fileinfo[5],
        "height" => $fileinfo[6],
        "vc" => $fileinfo[8],
        "name" => $fileinfo[4]);
    return $finfo;
}






function convertit($infile,$outfile, $aform, $abr, $mbr){
    if (file_exists($outfile)){ print "File already exists, skipping.\n"; continue; } else {
        $cmd = "ffmpeg -i \"" . $infile . "\" -c:v hevc_nvenc -rc vbr_hq -b:v " . $abr . " -maxrate:v " . $mbr . " -rc-lookahead 32 -g 600 -c:a " . $aform . " \"$outfile\"";
        exec($cmd);
        sleep(1);
    }
}


function moveit ($oldfile, $newfile, $status){
    if (!file_exists("./Completed/")) {
        exec("mkdir Completed", $rdata);
    }
    if (!file_exists("./Converted/")) {
        exec("mkdir Converted", $rdata);
    }
    if (!file_exists("./Skipped/")) {
        exec("mkdir Skipped", $rdata);
    }
    if ($status=="Skip") {
        rename($oldfile, "./Skipped/" . $oldfile);
    }
    if ($status=="Completed") {
            rename($oldfile, "./Completed/" . $oldfile);
            rename($newfile, "./Converted/" . $newfile);
    }
}







?>
