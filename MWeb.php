<?php
/**
 * MWeb 本地图片上传到 Github 服务
 * User: TingV
 * Date: 2019-05-31
 * Time: 06:10
 */
$owner = 'tingv';
$repo = 'image';
$branch = 'MWeb';
$token = 'Personal access tokens';
$upload_path = date('Y').'/'.date('m').'/'.date('d');

$file_info = $_FILES['file'];
$file_name = $file_info['name'];
$tmp_name = $file_info['tmp_name'];
$error = $file_info['error'];

$extension = pathinfo($file_name,PATHINFO_EXTENSION);

$save_name = date('YmdHis') . '.' . $extension;

$file_path = __DIR__ . '/' . $save_name;

if ($error !== UPLOAD_ERR_OK){
    die("文件 " . $file_name . " 上传失败");
}

if(move_uploaded_file($tmp_name, $save_name) == false){
    die("文件 " . $file_name . " 上传失败");
}

$content = base64EncodeImage($file_path);

$headers = [
    'Authorization: token ' . $token,
    'Content-Type: application/json',
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
];

$data = json_encode([
    'branch'=> $branch,
    'message'=> 'Upload by MWeb',
    'content'=> $content,
]);

$url = "https://api.github.com/repos/{$owner}/{$repo}/contents/{$upload_path}/{$save_name}";

$data = request($url, $headers, $data);

@unlink($file_path);

header('Content-Type:application/json; charset=utf-8');
echo $data;

function request($url, $headers, $data)
{
    $handle = curl_init();
    curl_setopt($handle,CURLOPT_URL, $url);
    curl_setopt($handle,CURLOPT_HEADER,0);
    curl_setopt($handle,CURLOPT_HTTPHEADER, $headers);
    curl_setopt($handle,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($handle,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($handle,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($handle,CURLOPT_CUSTOMREQUEST,'PUT');
    curl_setopt($handle,CURLOPT_POSTFIELDS, $data);
    $data = curl_exec($handle);
    curl_close($handle);
    return $data;
}

function base64EncodeImage($image_file) {
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = base64_encode($image_data);
    return $base64_image;
}