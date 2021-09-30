<?php
require_once 'config.php';

 if(isset($_POST['videoSubmit'])){
    // Video info
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $tags = $_POST['tags'];
    $privacy = !empty($_POST['privacy'])?$_POST['privacy']:'public';
    $drivelink = $_POST['drive-link'];

    if($title == "Test Video Upload" && $desc=="Uploading a test video file" && $tags=="test"
    && $privacy=="public" && $drivelink == "https://drive.google.com/file/d/19elggHljKhjZ5OPhDON2KYIC0p3EO5Bu/view?usp=sharing"){
        if($_FILES['drive-link']['name']!= ""){
            $filesize = $_FILES['drive-link']['size'];
            $fileType = $_FILES['drive-link']['type'];
            $fileName = str_shuffle('nirma').'-'.basename($_FILES['drive-link']['name']);

            $targetDir = "videos/"; 
            $targetFile = $targetDir . $fileName; 

            $allowedTypeArr = array("video/mp4", "video/avi", "video/mpeg", "video/mpg", "video/mov", "video/wmv", "video/rm"); 
            if(in_array($fileType, $allowedTypeArr)) { 

                if(move_uploaded_file($_FILES['videoFile']['tmp_name'], $targetFile)) { 
            
                    $videoFilePath = $targetFile; 
            
                }else{ 
            
                    header('Location:'.BASE_URI.'index.php?err=ue'); 
            
            exit; 
            
                } 
            
            }else{ 
            
            header('Location:'.BASE_URI.'index.php?err=fe'); 
            
            exit; 
            
            } 

            echo('verified details');

        }
    }
    echo("try again");

    if (isset($_GET['code'])) { 

        if (strval($_SESSION['state']) !== strval($_GET['state'])) { 
        
          die('The session state did not match.'); 
        
        } 

        $client->authenticate($_GET['code']); 

        $_SESSION['token'] = $client->getAccessToken(); 

        header('Location: ' . REDIRECT_URI); 

        } 

        if (isset($_SESSION['token'])) { 

        $client->setAccessToken($_SESSION['token']); 

        } 

        $htmlBody = ''; 
        if ($client->getAccessToken()) { 

            try{ 
                $videoPath = $result['video_path']; 
                $snippet = new Google_Service_YouTube_VideoSnippet(); 
                $snippet->setTitle($result['video_title']); 
                $snippet->setDescription($result['video_description']); 
                $snippet->setTags(explode(",",$result['video_tags']));
                
                $snippet->setCategoryId("22"); 

                $status = new Google_Service_YouTube_VideoStatus(); 

                $status->privacyStatus = "public"; 

                $video = new Google_Service_YouTube_Video(); 

                $video->setSnippet($snippet); 

                $video->setStatus($status);

                $chunkSizeBytes = 1 * 1024 * 1024; 

                $client->setDefer(true); 

                $insertRequest = $youtube->videos->insert("status,snippet", $video); 

                //related to upload video?? needed??
                $media = new Google_Http_MediaFileUpload( 

                    $client, 
            
                    $insertRequest, 
            
                    'video/*', 
            
                    null, 
            
                    true, 
            
                    $chunkSizeBytes 
            
                ); 
                $media->setFileSize(filesize($videoPath)); 

                $status = false; 

                $handle = fopen($videoPath, "rb"); 

                while (!$status && !feof($handle)) { 

                 $chunk = fread($handle, $chunkSizeBytes); 

                $status = $media->nextChunk($chunk); 

                fclose($handle); 

                $client->setDefer(false);

                $db->update($result['video_id'],$status['id']); 

                $htmlBody .= "<p class='succ-msg'>Upload success.</p><ul>"; 

                $htmlBody .= '<embed width="400" height="315" src="https://www.youtube.com/embed/'.$status['id'].'"></embed>'; 

                $htmlBody .= '<li><b>Title: </b>'.$status['snippet']['title'].'</li>'; 

                $htmlBody .= '<li><b>Description: </b>'.$status['snippet']['description'].'</li>'; 

                $htmlBody .= '<li><b>Tags: </b>'.implode(",",$status['snippet']['tags']).'</li>'; 

                $htmlBody .= '</ul>'; 

                } catch (Google_ServiceException $e) { 

                $htmlBody .= sprintf('<p> service error : <code>%s</code></p>', 

                htmlspecialchars($e->getMessage())); 

                } catch (Google_Exception $e) { 

                $htmlBody .= sprintf('<p> client error: <code>%s</code></p>', htmlspecialchars($e->getMessage())); 

                $_SESSION['token'] = $client->getAccessToken(); 

                }
                $state = mt_rand(); 

                $client->setState($state); 

                $_SESSION['state'] = $state; 

   

                $authUrl = $client->createAuthUrl(); 

                $htmlBody = <<<END 
                <h3>Authorization Required</h3> 

                <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p> 

                END; 

} 

?>