# CmM
A small project I made in 12 hours. Again, using Nvidia GPU to convert movies etc to HEVC

The source code is supplied in php format.
This script executes external programs and requires the following to work.
  ffmpeg.exe - any version should be fine
  mediainfo.exe - again, version shouldn't matter.
  
Place your movie files in a folder, put the exe, the dll and the above programs in there.
run cmm_CmM.exe - accept the warning if running first time (source code is here, its not dangerous)

The following takes place
It will scan the folder for mkv, mp4 or avi files.
Using mediainfo it will extract some info.
It will direct stream copy any audio track except older mp3.
mp3 gets converted to aac, the rest gets stream copied.
It calculates a bitrate around 50% of the original to do the encoding. 
It creates a new file with HEVC & Audio tags in the title
ffmpeg runs backgrounded (maybe changed)
Processed files get moved to respective folders afterwards.
