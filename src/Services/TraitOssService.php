<?php

namespace Swoolecan\Foundation\Services;

trait TraitOssService
{
    public function batchLocal()
    {
        $path = '/data/htmlwww/filesys/culture/';
        $url = 'http://upfile.canliang.wang/culture/';
        $files = scandir($path);
        $sql = '';
        foreach ($files as $dir) {
            if (in_array($dir, ['.', '..'])) {
                continue;
            }
            if ($dir != 'book') {
                continue;
            }
            $subFiles = scandir($path . '/' . $dir);
            foreach ($subFiles as $subFile) {
                if (in_array($subFile, ['.', '..'])) {
                    continue;
                }
                $fileUrl = $url . $dir . '/' . $subFile;
                $filePath = $path . '/' . $dir . '/' . $subFile;
                $sql .= $this->checkAttachment($service, $subFile, $fileUrl, $filePath);

                //echo "<a href='{$fileUrl}' target='_blank'>{$fileUrl}</a><br />";
            }
            //print_R($subFiles);
        }
        print_r($files);exit();
    }

    public function putFileToRemote($fileName, $filePath)
    {
        $fileinfo = $this->fileData($filePath);
        //print_r($fileinfo);exit();
        //echo "{$file}=====<img src='{$fileUrl}' width='200px' height='200px' /><br />";
        $ext = $fileinfo['getExtension'];
        $aData = [
            'path_id' => 1422, 
            'name' => str_replace(".{$ext}", '', $fileName),
            'filename' => $fileName, 
            'mime_type' => $fileinfo['getMimeType'], 
            'extension' => $fileinfo['getExtension'],
            'size' => $fileinfo['getSize'],
        ];
        $filePut = 'book/cover_scholarism6/' . Str::uuid() . ".{$ext}";
        $attachment = $this->putFile($aData, $filePut, $fileUrl);
        return $attachment;
    }

    protected function checkAttachmentExist($service, $file, $fileUrl, $filePath)
    {
        static $i = 1;
        $model = $this->getModelObj('attachment');
        $infoModel = $this->getModelObj('attachmentInfo');
        $info = $model->where(['filename' => $file])->first();
        if (!strrpos($file, '.')) {
            echo $file;exit();
        }
        $baseFile = substr($file, 0, strrpos($file, '.'));
        $baseFile = substr($baseFile, intval(strrpos($baseFile, '??')));
        $baseFile = str_replace('??', '', $baseFile);
        //echo $file . '-' . $baseFile;exit();
        $figureModel = $this->getModelObj('culture-figure');
        $figures = $figureModel->where('name', 'like', "%{$baseFile}%")->get();
        if ($info) {
            $attachmentInfo = $infoModel->where('attachment_id', $info['id'])->first();
            //if (empty($attachmentInfo)) {
            //echo "{$i}-<a href='http://ossfile.canliang.wang/{$info['filepath']}' target='_blank'>yyyyy<a>-" . $info['name'] . '===' . $info['filepath'] . "<img src='http://ossfile.canliang.wang/{$info['filepath']}' width='200px' height='200px' />==<img src='{$fileUrl}' width='200px' height='200px' /><br />";
            $i++;
            //}
        } else {
            //$info = $model->where('name', 'like', "%{$baseFile}%")->first();
            if (empty($info)) {
            } else {
            //echo "<a href='http://ossfile.canliang.wang/{$info['filepath']}' target='_blank'>yyyyy<a>-" . $info['name'] . '===' . $info['filepath'] . "<img src='http://ossfile.canliang.wang/{$info['filepath']}' width='200px' height='200px' />==<img src='{$fileUrl}' width='200px' height='200px' /><br />";
            //echo $i . '--' . "<a href='{$fileUrl}' target='_blank'>{$fileUrl}</a><br />";
            }
            //$service->putFileToRemote($file, $filePath);
            
        }
        return true;
    }

    protected function fileData($filePath)
    {
        $file = new \SplFileInfo($filePath);
        $finfo = new \Finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);
        return [
            'getATime' => $file->getATime(), //??????????????????
            'getBasename' => $file->getBasename(), //??????????????????basename
            'getCTime' => $file->getCTime(), //??????inode????????????
            'getExtension' => $file->getExtension(), //???????????????
            'getFilename' => $file->getFilename(), //???????????????
            'getGroup' => $file->getGroup(), //???????????????
            'getInode' => $file->getInode(), //????????????inode
            //'getLinkTarget' => $file->getLinkTarget(), //??????????????????????????????
            'getMTime' => $file->getMTime(), //????????????????????????
            'getOwner' => $file->getOwner(), //???????????????
            'getPath' => $file->getPath(), //??????????????????????????????
            'getPathInfo' => $file->getPathInfo(), //???????????????SplFileInfo??????
            'getPathname' => $file->getPathname(), //?????????
            'getPerms' => $file->getPerms(), //????????????
            'getRealPath' => $file->getRealPath(), //??????????????????
            'getSize' => $file->getSize(),//???????????????????????????
            'getType' => $file->getType(),//???????????? file  dir  link
            'getMimeType' => $mimeType,
            'isDir' => $file->isDir(), //???????????????
            'isFile' => $file->isFile(), //???????????????
            'isLink' => $file->isLink(), //?????????????????????
            'isExecutable' => $file->isExecutable(), //???????????????
            'isReadable' => $file->isReadable(), //????????????
            'isWritable' => $file->isWritable(), //????????????
        ];
    }
}
