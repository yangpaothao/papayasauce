<?php
namespace PapayasauceClasses;
require_once __DIR__ . '/../vendor/autoload.php';
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
/**
 * This class will return a resized video, making the video smaller in size while trying to keep the quality.
 *Composer does not install the actual FFmpeg executable system program; it only installs PHP wrapper libraries 
 * (such as php-ffmpeg/php-ffmpeg) that allow your PHP code to interact with an already existing FFmpeg installation 
 * on your machine.Because Composer only downloads the PHP codebase into your vendor/ directory, your system or 
 * PHP library will throw an error saying it cannot find the executable.
 * 
 * To fix this, you must install the actual binary onto your operating system manually and tell your PHP code where it is.
 * 
 * Open cmd, cd dir to the folder you want to install the excutable, then type in
 * winget install ffmpeg
 * After install, you can check ffmpeg -version and if you get stuffs back, you did it.
 * 
 * 
 * DUE TO DEPRACATION, HAD TO CHANGE THESE TWO LINES FROM ATTACH() TO OFFSETsET IN lISTENERS.PHP IN vendor/php-ffmpeg, just do a search for attach( and
 * you will find this file in this dir.
 * //$this->storage->attach($listener, $EElisteners);
   $this->storage->offsetSet($listener, $EElisteners);
 * 
 * To get the exe, google and search 'php ffmpeg manually download the executable'
 * will take you to a site https://www.gyan.dev/ffmpeg/builds/ where you can download the exe and you can put in the dir you like
 * on the server
 * 
 * When it comes to local host, you can just install using cmd, just cd to the proper dir you like the exe to.
 * @author Yang Pao Thao
 */
class VideoClass {
    private $video = "";
    private $filename = "";
    private $dir = "";
    private $myhost = "";
    function SetVideo($tempdir, $tempfilename, $tempvideo, $temp_host)
    {
        $this->dir = "$tempdir";
        $this->filename = $tempfilename;
        $this->video = $tempvideo;
        $this->myhost = $temp_host;
    }
    function GetVideo()
    {
        // Initialize FFmpeg (will auto-detect system binaries)
        //We had to follow 7-17 above to install the excutable for this manual dir declaration for this to work.
        
        //public_html/website_ad583fcd/Common/vendor/php-ffmpeg/php-ffmpeg/src/ffmpeg-exe/bin
        $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => '/home1/gcwwkite/public_html/website_ad583fcd/Common/vendor/php-ffmpeg/php-ffmpeg/src/ffmpeg-exe/bin/ffmpeg.exe',
                'ffprobe.binaries' => '/home1/gcwwkite/public_html/website_ad583fcd/Common/vendor/php-ffmpeg/php-ffmpeg/src/ffmpeg-exe/bin/ffprobe.exe',
                'timeout' => 3600
        ]);
        
        // Open the target video
        $video = $ffmpeg->open($this->video);

        // Apply the resize filter
        $video->filters()->resize(
            new Dimension(1920, 1080), 
            ResizeFilter::RESIZEMODE_FIT, // Modes: FIT (adds padding if needed), SCALE_WIDTH, or SCALE_HEIGHT
            true                          // Force to keep aspect ratio
        );
        
        
        $format = new X264();
        // Inject custom high-quality parameters into the format framework
        $format->setAdditionalParameters([
            '-vf', 'scale=1280:-2:flags=lanczos',
            '-crf', '18',
            '-preset', 'slow',
            '-c:a', 'copy'
        ]);
        
        
        //file_put_contents("./dodebug/debug.txt", "dir: ".$this->dir."/".$this->filename."\n", FILE_APPEND);
        try{
           //$video->save(new \FFMpeg\Format\Video\X264('aac', 'libx264'), $this->dir."/".$this->filename);
           $video->save($format, $this->dir."/".$this->filename);
           return("Success");
        }
        catch (\Exception $e) {
            file_put_contents("./dodebug/debug.txt", "Video Convert Err: ".$e->getMessage()."\n", FILE_APPEND);
            //echo $e->getMessage(); // This outputs the exact terminal command and error
        }
    }
}?>
