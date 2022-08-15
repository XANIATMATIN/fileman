<?php

function ChunkUploadUrls($urls)
{
    return app('fileman')->ChunkUploadUrls($urls);
}

function uploadUrl($file, $isImage)
{
    return app('fileman')->uploadUrl($file, $isImage);
}

function createFolder(string $type)
{
    return app('fileman')->createFolder($type);
}

function uploadDoc(string $filePath)
{
    return app('fileman')->uploadDoc($filePath);
}

function fetchUrl(string $date, string $token, string $originalUrl, string $method = 'POST')
{
    return app('fileman')->fetchUrl($date,  $token,  $originalUrl, $method);
}