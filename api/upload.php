<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 从Vercel环境变量获取配置
$githubUser = getenv('GITHUB_USER');
$githubRepo = getenv('GITHUB_REPO');
$githubToken = getenv('GITHUB_TOKEN');
$githubBranch = 'main';

// 检查环境变量是否都已设置
if (!$githubUser || !$githubRepo || !$githubToken || !$githubBranch) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => '服务器环境变量未正确配置。请检查Vercel项目的环境变量设置。']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => '只允许 POST 请求。']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['filename']) || !isset($data['content'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => '缺少文件名或文件内容。']);
    exit();
}

$filename = basename($data['filename']);
$fileContent = $data['content'];

$pathInRepo = 'images/' . $filename;
$githubApiUrl = "https://api.github.com/repos/{$githubUser}/{$githubRepo}/contents/{$pathInRepo}";

$requestBody = json_encode([
    'message' => 'Upload image ' . $filename,
    'content' => $fileContent,
    'branch' => $githubBranch
]);

$ch = curl_init($githubApiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Vercel-GitHub-Image-Uploader',
    'Authorization: token ' . $githubToken,
    'Content-Type: application/json',
    'Accept: application/vnd.github.v3+json'
]);

// 在Vercel环境中不需要关闭SSL验证

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 || $httpCode === 201) {
    $responseData = json_decode($response, true);
    $imageUrl = $responseData['content']['download_url'] ?? null;
    if ($imageUrl) {
        echo json_encode(['success' => true, 'message' => '图片上传成功！', 'imageUrl' => $imageUrl]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '上传成功但未获取到图片链接。', 'response' => $responseData]);
    }
} else {
    http_response_code($httpCode > 0 ? $httpCode : 500);
    $responseData = json_decode($response, true);
    $errorMessage = 'GitHub API 请求失败: ' . ($responseData['message'] ?? $error);
    echo json_encode(['success' => false, 'message' => $errorMessage, 'httpCode' => $httpCode, 'response' => $responseData ?? null]);
}

?> 