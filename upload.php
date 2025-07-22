<?php

header('Content-Type: application/json');

// 允许跨域请求，根据实际情况调整
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理 OPTIONS 请求（CORS 预检请求）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 从 config.json 读取配置
$configFilePath = __DIR__ . '/config.json';
if (!file_exists($configFilePath)) {
    echo json_encode(['success' => false, 'message' => 'config.json 文件不存在。请确保已正确配置。']);
    exit();
}
$config = json_decode(file_get_contents($configFilePath), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'config.json 文件解析失败。请检查 JSON 格式。']);
    exit();
}

$githubUser = $config['githubUser'] ?? null;
$githubRepo = $config['githubRepo'] ?? null;
$githubToken = $config['githubToken'] ?? null;
$githubBranch = $config['githubBranch'] ?? null;
$imagePath = $config['imagePath'] ?? null;

// 检查必要的配置项是否存在
if (!$githubUser || !$githubRepo || !$githubToken || !$githubBranch || !$imagePath) {
    echo json_encode(['success' => false, 'message' => 'config.json 中缺少必要的 GitHub 配置信息（githubUser, githubRepo, githubToken, githubBranch, imagePath）。']);
    exit();
}

// 检查是否是 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '只允许 POST 请求。']);
    exit();
}

// 获取 POST 请求的原始数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 检查数据是否有效
if (!isset($data['filename']) || !isset($data['content'])) {
    echo json_encode(['success' => false, 'message' => '缺少文件名或文件内容。']);
    exit();
}

$filename = $data['filename'];
$fileContent = $data['content']; // Base64 编码的图片内容

// 确保文件名是安全的，避免路径遍历等问题
$filename = basename($filename);

// 构建 GitHub API URL
// 图片将上传到仓库的根目录，您可以根据需要修改路径
$pathInRepo = $imagePath . '/' . $filename; // 例如，上传到 images 文件夹下
$githubApiUrl = "https://api.github.com/repos/{$githubUser}/{$githubRepo}/contents/{$pathInRepo}";

// 构建请求体
$requestBody = json_encode([
    'message' => 'Upload image ' . $filename,
    'content' => $fileContent, // Base64 编码的内容
    'branch' => $githubBranch
]);

// 设置 cURL 选项
$ch = curl_init($githubApiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: PHP-GitHub-Image-Uploader',
    'Authorization: token ' . $githubToken,
    'Content-Type: application/json',
    'Accept: application/vnd.github.v3+json'
]);
// 临时关闭SSL证书验证（仅测试用，生产环境请配置CA证书）
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// 执行 cURL 请求
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 处理响应
if ($httpCode === 200 || $httpCode === 201) {
    $responseData = json_decode($response, true);
    // GitHub 返回的 download_url 是原始文件链接
    $imageUrl = $responseData['content']['download_url'] ?? null;

    if ($imageUrl) {
        echo json_encode(['success' => true, 'message' => '图片上传成功！', 'imageUrl' => $imageUrl]);
    } else {
        echo json_encode(['success' => false, 'message' => '上传成功但未获取到图片链接。', 'response' => $responseData]);
    }
} else {
    $errorMessage = 'GitHub API 请求失败。';
    if ($error) {
        $errorMessage .= ' cURL 错误: ' . $error;
    } else {
        $responseData = json_decode($response, true);
        $errorMessage .= ' HTTP 状态码: ' . $httpCode . '. 错误信息: ' . ($responseData['message'] ?? '未知错误');
    }
    echo json_encode(['success' => false, 'message' => $errorMessage, 'httpCode' => $httpCode, 'response' => $responseData ?? null]);
}

?>