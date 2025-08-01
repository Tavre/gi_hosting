export default {
  async fetch(request, env, ctx) {
    // CORS 处理
    if (request.method === 'OPTIONS') {
      return new Response('', {
        status: 204,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'POST, GET, OPTIONS',
          'Access-Control-Allow-Headers': 'Content-Type, Authorization',
        },
      });
    }

    // 只允许 POST
    if (request.method !== 'POST') {
      return new Response(JSON.stringify({ success: false, message: '只允许 POST 请求。' }), {
        status: 405,
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      });
    }

    // 解析请求体
    let data;
    try {
      data = await request.json();
    } catch (e) {
      return new Response(JSON.stringify({ success: false, message: '请求体不是有效的JSON。' }), {
        status: 400,
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      });
    }

    const { filename, content } = data;
    if (!filename || !content) {
      return new Response(JSON.stringify({ success: false, message: '缺少文件名或文件内容。' }), {
        status: 400,
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      });
    }

    // 配置（请替换为你的真实信息）
    const githubUser = '你的GitHub用户名'; // TODO: 替换为你的 GitHub 用户名
    const githubRepo = '你的仓库名';      // TODO: 替换为你的仓库名
    const githubToken = '你的GitHub Token'; // TODO: 替换为你的 GitHub Token
    const githubBranch = 'main';
    const pathInRepo = `images/${filename}`;
    const githubApiUrl = `https://api.github.com/repos/${githubUser}/${githubRepo}/contents/${pathInRepo}`;

    // 调用 GitHub API 上传
    const githubResponse = await fetch(githubApiUrl, {
      method: 'PUT',
      headers: {
        'Authorization': `token ${githubToken}`,
        'User-Agent': 'Cloudflare-Worker-GitHub-Uploader',
        'Content-Type': 'application/json',
        'Accept': 'application/vnd.github.v3+json',
      },
      body: JSON.stringify({
        message: `Upload image ${filename}`,
        content: content,
        branch: githubBranch,
      }),
    });

    const githubResult = await githubResponse.json();

    if (githubResponse.status === 201 || githubResponse.status === 200) {
      const imageUrl = githubResult.content?.download_url;
      return new Response(JSON.stringify({ success: true, message: '图片上传成功！', imageUrl }), {
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      });
    } else {
      return new Response(JSON.stringify({
        success: false,
        message: githubResult.message || 'GitHub API 请求失败',
        response: githubResult,
      }), {
        status: 500,
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      });
    }
  }
}