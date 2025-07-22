# Gi Hosting

这是一个基于 PHP、HTML、JavaScript 和 CSS 的 GitHub 图床工具，支持多图片/视频上传、jsDelivr CDN 加速、画廊展示等功能。

## 功能

- 拖拽或选择多张图片/视频文件批量上传
- 上传进度条实时显示
- 仅允许上传图片和视频类型文件，自动过滤其他类型
- 上传成功后自动生成 jsDelivr CDN 链接，并支持图片/视频预览
- 支持 GitHub 仓库 images 目录下所有图片的画廊展示（gallery.html）
- 画廊页面支持缩略图、点击放大预览、底部显示 jsDelivr 链接
- 上传页一键跳转画廊页
- 响应式设计，适配手机和PC

## 目录结构

```
├── LICENSE         # 开源许可证
├── README.md       # 项目说明文档
├── config.json     # 配置文件，包含GitHub仓库信息
├── gallery.css     # 画廊页面专用样式
├── gallery.html    # 图床画廊页面
├── index.html      # 上传页面
├── main.js         # 上传页面交互脚本
├── style.css       # 通用样式
└── upload.php      # 处理上传并推送到GitHub的后端脚本
```

## 使用方法

### 方案一：本地或自有服务器部署

1. **准备环境**
   - 确保你的服务器或本地环境已安装 PHP（建议 7.2 及以上）并启用 curl 扩展。
   - 建议使用 Nginx、Apache、IIS 等常见 Web 服务器。
2. **下载项目文件**
   - 将本项目的所有文件（`index.html`、`main.js`、`style.css`、`upload.php`、`gallery.html`、`gallery.css`、`README.md` 等）放置到你的 Web 服务器根目录或子目录下。
3. **配置 `config.json`**
   - 打开 `config.json`，根据你的 GitHub 仓库和 Token 修改相关配置项：
     - `githubUser`：你的 GitHub 用户名
     - `githubRepo`：你的图床仓库名
     - `githubToken`：你的 GitHub Token（需有 repo 权限）
     - `githubBranch`：主分支名（如 main 或 master）
     - `imagePath`：图片在仓库中存储的路径（例如：`images`）
   - `upload.php` 会自动从 `config.json` 读取这些配置。
4. **设置文件夹权限**
   - 确保 PHP 进程有权限访问项目目录。
5. **访问上传页面**
   - 在浏览器中输入 `http://localhost/index.html` 或 `http://你的服务器域名/目录/index.html`。
6. **上传图片/视频**
   - 拖放或选择多张图片/视频文件，点击“开始上传”按钮。
   - 上传过程中会显示进度条，实时反馈上传进度。
   - 上传成功后，页面会显示 jsDelivr CDN 链接和图片/视频预览。
   - 仅支持图片和视频类型文件，其他类型会被自动过滤。
7. **跳转画廊页面**
   - 点击“查看图床画廊”按钮可跳转到 gallery.html，浏览仓库所有图片。
8. **浏览画廊**
   - gallery.html 会自动从 GitHub 仓库 images 目录获取所有图片，通过 jsDelivr CDN 展示缩略图。
   - 支持点击图片放大预览，底部显示可复制的 jsDelivr 链接。
9. **常见问题排查**
   - 若上传失败，请检查 Token 权限、仓库配置、PHP 错误日志、网络连通性等。
   - 建议不要将 Token 暴露在公网环境，生产环境请用更安全的配置方式。
   - 如遇 SSL 证书报错，请参考 README 前部“准备工作”说明配置 CA 证书。


## 画廊页面说明
- gallery.html 会自动从 GitHub 仓库 images 目录获取所有图片，通过 jsDelivr CDN 展示缩略图
- 支持点击图片放大预览，底部显示可复制的 jsDelivr 链接
- gallery.css 为画廊页面专用样式

## 开源协议
MIT License
