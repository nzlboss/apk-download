<?php
// 确保脚本不会超时
set_time_limit(300);

// 获取文件名参数
$fileName = isset($_GET['file']) ? $_GET['file'] : '';

// 检查文件名是否有效
if (empty($fileName) || !preg_match('/^XMSX\d+\.apk$/', $fileName)) {
    die('无效的文件请求');
}

// 构建文件路径
$filePath = 'uploads/' . $fileName;

// 检查文件是否存在
if (!file_exists($filePath)) {
    die('文件不存在或已被删除');
}

// 获取用户代理
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 检测设备和浏览器类型
$isIOS = (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false || strpos($ua, 'iPod') !== false);
$isAndroid = (strpos($ua, 'Android') !== false);
$isWechat = (strpos($ua, 'MicroMessenger') !== false);

// 初始化页面数据
$pageData = [
    'isIOS' => $isIOS,
    'isAndroid' => $isAndroid,
    'isWechat' => $isWechat,
    'targetUrl' => $filePath,
    'fileName' => $fileName
];
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应用下载</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#10B981',
                        accent: '#8B5CF6',
                        dark: '#1E293B',
                        light: '#F8FAFC'
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .shadow-soft {
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }
            .bg-gradient-primary {
                background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            }
            .transition-all-300 {
                transition: all 300ms ease-in-out;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-inter text-gray-800 min-h-screen flex flex-col">
    <!-- 导航栏 -->
    <header class="bg-white shadow-md sticky top-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-cloud-download text-primary text-2xl"></i>
                <h1 class="text-xl font-bold text-dark">应用<span class="text-primary">下载</span></h1>
            </div>
        </div>
    </header>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <section class="max-w-2xl mx-auto">
            <!-- 标题区域 -->
            <div class="text-center mb-8">
                <h2 class="text-[clamp(1.5rem,3vw,2rem)] font-bold text-dark mb-3">APK下载</h2>
                <p class="text-gray-600">准备下载应用程序，请根据您的设备选择合适的方式</p>
            </div>

            <!-- 应用信息卡片 -->
            <div class="bg-white rounded-xl shadow-soft overflow-hidden mb-8 transform transition-all duration-300 hover:shadow-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6 items-center">
                        <div class="bg-blue-100 p-4 rounded-xl">
                            <i class="fa-brands fa-android text-primary text-4xl"></i>
                        </div>
                        <div class="flex-grow text-center md:text-left">
                            <h3 class="text-xl font-semibold mb-2">Android应用</h3>
                            <p class="text-gray-600 mb-3">文件: <?php echo htmlspecialchars($pageData['fileName']); ?></p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3">
                                <button id="download-btn" class="bg-primary hover:bg-primary/90 text-white font-medium py-2 px-6 rounded-lg transition-all duration-300 flex items-center">
                                    <i class="fa-solid fa-download mr-2"></i> 立即下载
                                </button>
                                <button id="share-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg transition-all duration-300 flex items-center">
                                    <i class="fa-solid fa-share-alt mr-2"></i> 分享链接
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 设备提示区域 -->
            <div id="device-message" class="hidden bg-white rounded-xl shadow-soft p-6 mb-8">
                <div class="flex items-start space-x-4">
                    <div id="message-icon" class="bg-blue-100 p-3 rounded-full">
                        <i class="fa-solid fa-info-circle text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 id="message-title" class="text-xl font-semibold mb-2">提示</h3>
                        <div id="message-content">
                            <!-- 内容将通过JavaScript动态填充 -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-8">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p class="text-gray-400">© 2025 APK下载系统 - 保留所有权利</p>
            </div>
        </div>
    </footer>

    <!-- 分享链接模态框 -->
    <div id="share-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">分享下载链接</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="share-url" readonly 
                    class="w-full bg-transparent border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                    value="<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . '/' . pathinfo($pageData['fileName'], PATHINFO_FILENAME) . '.php'); ?>"
            </div>
            <div class="grid grid-cols-4 gap-3 mb-4">
                <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg text-center transition-all duration-300">
                    <i class="fa-brands fa-weixin text-xl"></i>
                </a>
                <a href="#" class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-lg text-center transition-all duration-300">
                    <i class="fa-brands fa-whatsapp text-xl"></i>
                </a>
                <a href="#" class="bg-blue-400 hover:bg-blue-500 text-white p-3 rounded-lg text-center transition-all duration-300">
                    <i class="fa-brands fa-twitter text-xl"></i>
                </a>
                <a href="#" class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-lg text-center transition-all duration-300">
                    <i class="fa-brands fa-telegram text-xl"></i>
                </a>
            </div>
            <button id="copy-share-url" class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-2 px-6 rounded-lg transition-all duration-300">
                <i class="fa-solid fa-copy mr-1"></i> 复制链接
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 从PHP获取页面数据
            const pageData = <?php echo json_encode($pageData); ?>;
            
            const downloadBtn = document.getElementById('download-btn');
            const shareBtn = document.getElementById('share-btn');
            const deviceMessage = document.getElementById('device-message');
            const messageIcon = document.getElementById('message-icon');
            const messageTitle = document.getElementById('message-title');
            const messageContent = document.getElementById('message-content');
            const shareModal = document.getElementById('share-modal');
            const modalContent = document.getElementById('modal-content');
            const closeModal = document.getElementById('close-modal');
            const copyShareUrl = document.getElementById('copy-share-url');
            const shareUrl = document.getElementById('share-url');
            
            // 初始化设备提示
            if (pageData.isIOS) {
                // iOS用户提示
                showMessage('请在浏览器中打开此链接以继续', 'info', 'iOS设备检测');
            } else if (pageData.isAndroid && pageData.isWechat) {
                // 安卓微信用户特殊提示
                showAndroidWechatMessage();
            } else if (pageData.isWechat) {
                // 其他微信用户提示
                showMessage('请点击右上角菜单，选择"在浏览器中打开"', 'info', '微信浏览器检测');
            }
            
            // 下载按钮点击事件
            downloadBtn.addEventListener('click', () => {
                window.location.href = pageData.targetUrl;
            });
            
            // 分享按钮点击事件
            shareBtn.addEventListener('click', () => {
                shareModal.classList.remove('hidden');
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                    modalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            });
            
            // 关闭模态框
            closeModal.addEventListener('click', () => {
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    shareModal.classList.add('hidden');
                }, 300);
            });
            
            // 复制分享链接
            copyShareUrl.addEventListener('click', () => {
                shareUrl.select();
                document.execCommand('copy');
                
                // 更改按钮文本
                copyShareUrl.innerHTML = '<i class="fa-solid fa-check mr-1"></i> 已复制';
                setTimeout(() => {
                    copyShareUrl.innerHTML = '<i class="fa-solid fa-copy mr-1"></i> 复制链接';
                }, 2000);
            });
            
            // 显示通用提示信息
            function showMessage(message, type = 'info', title = '提示') {
                messageTitle.textContent = title;
                messageContent.innerHTML = `<p class="text-gray-600">${message}</p>`;
                
                // 设置图标和颜色
                messageIcon.innerHTML = '';
                messageIcon.className = '';
                
                if (type === 'success') {
                    messageIcon.innerHTML = '<i class="fa-solid fa-check text-green-500"></i>';
                    messageIcon.className = 'bg-green-100 p-3 rounded-full';
                } else if (type === 'error') {
                    messageIcon.innerHTML = '<i class="fa-solid fa-exclamation-circle text-red-500"></i>';
                    messageIcon.className = 'bg-red-100 p-3 rounded-full';
                } else {
                    messageIcon.innerHTML = '<i class="fa-solid fa-info-circle text-primary"></i>';
                    messageIcon.className = 'bg-blue-100 p-3 rounded-full';
                }
                
                // 显示消息区域
                deviceMessage.classList.remove('hidden');
            }
            
            // 显示安卓微信特殊提示
            function showAndroidWechatMessage() {
                messageTitle.textContent = '微信浏览器检测';
                messageContent.innerHTML = `
                    <p class="text-gray-600 mb-3">您正在使用微信浏览器访问</p>
                    <p class="text-gray-600 mb-3">微信内无法直接下载应用，请按以下步骤操作：</p>
                    <ol class="text-gray-600 list-decimal pl-5 space-y-2 mb-4">
                        <li>点击右上角"..."菜单</li>
                        <li>选择"在浏览器中打开"</li>
                        <li>在浏览器中点击下载按钮</li>
                    </ol>
                    <button onclick="window.location.href = pageData.targetUrl" class="bg-primary hover:bg-primary/90 text-white font-medium py-2 px-6 rounded-lg transition-all duration-300">
                        在浏览器中打开
                    </button>
                `;
                
                messageIcon.innerHTML = '<i class="fa-solid fa-mobile-alt text-primary"></i>';
                messageIcon.className = 'bg-blue-100 p-3 rounded-full';
                
                // 显示消息区域
                deviceMessage.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>    