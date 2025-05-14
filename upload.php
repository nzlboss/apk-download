<?php
// 确保脚本不会超时
set_time_limit(300);

// 上传目录配置
$uploadDir = 'uploads/';
// 确保上传目录存在
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 初始化响应数据
$response = [
    'success' => false,
    'message' => '',
    'downloadUrl' => '',
    'fileName' => ''
];

// 检查是否有文件上传
if (isset($_FILES['apkFile'])) {
    $file = $_FILES['apkFile'];
    
    // 检查文件上传是否成功
    if ($file['error'] === UPLOAD_ERR_OK) {
        // 获取文件扩展名
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // 验证文件类型
        if (strtolower($fileExt) === 'apk') {
            // 生成新的文件名（使用时间戳确保唯一性）
            $newFileName = 'XMSX' . time() . '.apk';
            $targetPath = $uploadDir . $newFileName;
            
            // 移动上传的文件
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // 创建对应的下载页面
                createDownloadPage($newFileName);
                
                $response['success'] = true;
                $response['message'] = 'APK上传成功！';
                $response['downloadUrl'] = 'download.php?file=' . $newFileName;
                $response['fileName'] = $newFileName;
            } else {
                $response['message'] = '无法保存上传的文件，请检查目录权限。';
            }
        } else {
            $response['message'] = '请上传APK格式的文件！';
        }
    } else {
        $response['message'] = '文件上传失败，错误代码：' . $file['error'];
    }
} else {
    $response['message'] = '未检测到上传的文件！';
}

// 返回JSON响应
header('Content-Type: application/json');
echo json_encode($response);

// 创建对应的下载页面
function createDownloadPage($fileName) {
    $downloadPageContent = '<?php
// 定义目标URL
$targetUrl = \'uploads/' . $fileName . '\';

// 获取用户代理
$ua = $_SERVER[\'HTTP_USER_AGENT\'] ?? \'\';

// 检测设备和浏览器类型
$isIOS = (strpos($ua, \'iPhone\') !== false || strpos($ua, \'iPad\') !== false || strpos($ua, \'iPod\') !== false);
$isAndroid = (strpos($ua, \'Android\') !== false);
$isWechat = (strpos($ua, \'MicroMessenger\') !== false);

// 初始化页面数据
$pageData = [
    \'isIOS\' => $isIOS,
    \'isAndroid\' => $isAndroid,
    \'isWechat\' => $isWechat,
    \'targetUrl\' => $targetUrl
];
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应用下载</title>
    <script>
        // 从PHP获取页面数据
        const pageData = <?php echo json_encode($pageData); ?>;
        
        // 页面加载后执行
        document.addEventListener(\'DOMContentLoaded\', function() {
            if (pageData.isIOS) {
                // iOS用户提示
                showMessage(\'请在浏览器中打开此链接以继续\');
            } else if (pageData.isAndroid && pageData.isWechat) {
                // 安卓微信用户特殊提示
                showAndroidWechatMessage();
            } else if (pageData.isWechat) {
                // 其他微信用户提示
                showMessage(\'请点击右上角菜单，选择"在浏览器中打开"\');
            } else {
                // 其他情况自动跳转
                redirectToApp();
            }
        });
        
        // 显示通用提示信息
        function showMessage(message) {
            const container = document.createElement(\'div\');
            container.className = \'message-container\';
            container.innerHTML = `
                <div class="message-box">
                    <div class="message-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="message-text">${message}</div>
                    <button class="message-button" onclick="redirectToApp()">继续</button>
                </div>
            `;
            document.body.appendChild(container);
        }
        
        // 显示安卓微信特殊提示
        function showAndroidWechatMessage() {
            const container = document.createElement(\'div\');
            container.className = \'message-container\';
            container.innerHTML = `
                <div class="message-box">
                    <div class="message-icon"><i class="fas fa-mobile-alt"></i></div>
                    <div class="message-text">
                        <p>您正在使用微信浏览器访问</p>
                        <p>微信内无法直接下载应用，请按以下步骤操作：</p>
                        <ol>
                            <li>点击右上角"..."菜单</li>
                            <li>选择"在浏览器中打开"</li>
                            <li>在浏览器中点击下载按钮</li>
                        </ol>
                    </div>
                    <button class="message-button" onclick="redirectToApp()">在浏览器中打开</button>
                </div>
            `;
            document.body.appendChild(container);
        }
        
        // 跳转到应用
        function redirectToApp() {
            window.location.href = pageData.targetUrl;
        }
    </script>
    <style>
        .message-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .message-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 80%;
        }
        
        .message-icon {
            font-size: 36px;
            color: #4285f4;
            margin-bottom: 15px;
        }
        
        .message-text {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .message-text p {
            margin-bottom: 10px;
        }
        
        .message-text ol {
            text-align: left;
            margin-left: 20px;
            padding-left: 0;
        }
        
        .message-button {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
</body>
</html>';

    file_put_contents(pathinfo($fileName, PATHINFO_FILENAME) . '.php', $downloadPageContent);
}
?>    