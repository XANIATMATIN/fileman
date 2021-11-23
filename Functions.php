<?php

function ChunkUploadUrls($urls)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => env("FILEMAN_HOST", "http://file-center.api") . "/check-url-array",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ['pid: ' . app('log-system')->getpid()],
        CURLOPT_POSTFIELDS => array('urls' => json_encode($urls), 'create' => 'true', 'type' => 'image'),
    ));

    $rawResponse = curl_exec($curl);
    $response = json_decode($rawResponse, true);
    if (!$rawResponse || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        lugWarning('ChunkUploadUrls() - Unable to upload file, Returning null', ['rawResponse' => $rawResponse, 'http-status-code' => curl_getinfo($curl, CURLINFO_HTTP_CODE)]);
        return [];
    }
    curl_close($curl);
    return $response;
}

function uploadUrl($file, $isImage)
{
    if (empty($file)) {
        lugWarning('No file to upload, Returning null');
        return '';
    }
    $url = base64_encode($file);
    $type = $isImage ? 'image' : 'document';
    lugInfo('Sending upload request', ['url' => env("FILEMAN_HOST", "http://file-center.api") . "/check-url?url=$url&create=true&type=$type", 'requestedFile' => $file, 'isImage' => $isImage, 'type' => $type]);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => env("FILEMAN_HOST", "http://file-center.api") . "/check-url?url=$url&create=true&type=$type",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => ['pid: ' . app('log-system')->getpid()],
    ));
    $rawResponse = curl_exec($curl);
    $response = json_decode($rawResponse);
    // lugInfo('Upload response', [ 'rawResponse' => $rawResponse]);
    if (!$rawResponse || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        lugWarning('Unable to upload file, Returning null');
        return '';
    }
    curl_close($curl);
    $downloadUrl = $response->downloadUrl . '/' . $response->fileName;
    lugInfo('Download Url', ['downloadUrl' => $downloadUrl, 'rawResponse' => $rawResponse]);
    return $downloadUrl;
}

function createFolder(string $type)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url = env("FILEMAN_HOST", "http://file-center.api") . "/create/$type",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => ['pid: ' . app('log-system')->getpid()],
    ));
    $rawResponse = curl_exec($curl);
    $response = json_decode($rawResponse);
    // lugInfo('create response', ['rawResponse' => $rawResponse]);
    if (!$rawResponse || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        app('log')->error('createFolder Error', ['url' => $url, 'response' => base64_encode($rawResponse ?? ''), 'code' => curl_getinfo($curl, CURLINFO_HTTP_CODE)]);
        lugError('Unable to upload file', [$url, curl_getinfo($curl, CURLINFO_HTTP_CODE), $response]);
        return '';
    }
    app('log-system')->send();
    curl_close($curl);
    return $response;
}

function uploadDoc(string $filePath)
{
    $downloadUrl = createFolder('document');
    $url = env("FILEMAN_HOST", "http://file-center.api") . "/upload/" . $downloadUrl->date . '/' . $downloadUrl->token;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ['pid: ' . app('log-system')->getpid()],
        CURLOPT_POSTFIELDS => ['file' => curl_file_create($filePath)]
    ));

    $rawResponse = curl_exec($curl);
    $response = json_decode($rawResponse);
    lugInfo('create response', ['rawResponse' => $rawResponse, 'file' => $filePath]);
    if (!$rawResponse || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        lugError('Unable to upload file', [$url, curl_getinfo($curl, CURLINFO_HTTP_CODE), $response]);
        return '';
    }
    curl_close($curl);
    app('log-system')->send();
    return;
}

function fetchUrl(string $date, string $token, string $originalUrl,string $method = 'POST')
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL =>  env('FILEMAN_HOST') . '/fetch-url/' . $date . '/' . $token,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => ['pid: ' . app('log-system')->getpid()],
        CURLOPT_POSTFIELDS => array('originalUrl' => $originalUrl, 'storeUrl' => false, 'method' => $method, 'async' => 'true'),
    ));
    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

