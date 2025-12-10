const API_URL = "api"; // Relative path works better if file structure is consistent

async function apiRequest(endpoint, method = "GET", body = null, isFormData = false) {
    const options = { method: method };
    if (body) {
        options.body = body;
        if (!isFormData) {
            options.headers = { "Content-Type": "application/json" };
            options.body = JSON.stringify(body);
        }
    }
    try {
        const response = await fetch(`${API_URL}/${endpoint}`, options);
        return await response.json();
    } catch (e) {
        console.error("API Error:", e);
        return { message: "Error de conexión" };
    }
}

// Time Ago Helper
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " años";

    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " meses";

    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " días";

    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " h";

    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " min";

    return "hace un momento";
}

// Global Session Check
async function checkSession() {
    try {
        const data = await apiRequest("auth/session.php");
        return data.logged_in ? data.user : null;
    } catch (e) {
        return null;
    }
}

// Auth Logic
const loginForm = document.getElementById('form-login');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await apiRequest("auth/login.php", "POST", {
            correo: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value
        });
        if (res.user_id) window.location.href = "index.html";
        else alert(res.message);
    });
}

const registerForm = document.getElementById('form-register');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await apiRequest("auth/register.php", "POST", {
            nombre: document.getElementById('reg-name').value,
            correo: document.getElementById('reg-email').value,
            password: document.getElementById('reg-password').value
        });
        if (res.message.includes("exitosamente")) {
            alert("Cuenta creada. Inicia sesión.");
            toggleAuth('login');
        } else alert(res.message);
    });
}

// Feed Rendering
const feedContainer = document.getElementById('feed-container');

async function loadPosts(userId = null) {
    if (!feedContainer) return;

    const endpoint = userId ? `posts/get_by_user.php?id=${userId}` : "posts/get_all.php";
    const data = await apiRequest(endpoint);

    if (data.records) {
        feedContainer.innerHTML = '';
        data.records.forEach(post => {
            const div = document.createElement('div');
            div.className = 'glass-card post-card';

            const userImg = post.autor_foto && post.autor_foto != 'default_profile.png' ? post.autor_foto : 'assets/img/default_avatar.png'; // Fallback logic
            const postImg = post.imagen ? `<img src="${post.imagen}" class="post-image">` : '';

            // Comments
            let commentsHtml = '';
            if (post.comentarios) {
                post.comentarios.forEach(c => {
                    commentsHtml += `
                        <div class="comment">
                            <img src="${c.foto_perfil || 'assets/img/default_avatar.png'}" class="comment-avatar">
                            <div class="comment-bubble">
                                <strong>${c.nombre}</strong> <span style="font-size:0.8em; color:#bbb">${timeAgo(c.fecha_creacion)}</span><br>
                                ${c.contenido}
                            </div>
                        </div>`;
                });
            }

            div.innerHTML = `
                <div class="post-header">
                    <img src="${userImg}" class="user-avatar" onerror="this.src='assets/img/default_avatar.png'">
                    <div class="post-info">
                        <h4>${post.autor}</h4>
                        <small>${timeAgo(post.fecha_creacion)}</small>
                    </div>
                </div>
                <div class="post-content">${post.contenido}</div>
                ${postImg}
                
                <div class="comments-section">
                    <div id="comments-${post.id}">${commentsHtml}</div>
                    <form onsubmit="postComment(event, ${post.id})" style="position:relative; margin-top:10px;">
                        <input type="text" id="input-${post.id}" placeholder="Escribe un comentario...">
                    </form>
                </div>
            `;
            feedContainer.appendChild(div);
        });
    }
}

async function postComment(e, postId) {
    e.preventDefault();
    const input = document.getElementById(`input-${postId}`);
    if (!input.value.trim()) return;

    const res = await apiRequest("comments/create.php", "POST", {
        publicacion_id: postId,
        contenido: input.value
    });

    if (res.message) {
        input.value = '';
        // Reload specific post comments or feed? reloading feed is easiest for "real time" feel
        // Usually we would just append the comment locally but requirement is "real time"
        // Let's rely on polling.
        // For immediate feedback:
        loadPosts(window.currentProfileId || null);
    }
}

// Create Post with Image
const publishForm = document.getElementById('publish-form');
if (publishForm) {
    publishForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = document.getElementById('post-content').value;
        const fileInput = document.getElementById('post-image-file');

        const formData = new FormData();
        formData.append('contenido', content);
        if (fileInput && fileInput.files[0]) {
            formData.append('imagen', fileInput.files[0]);
        }

        const res = await apiRequest("posts/create.php", "POST", formData, true);
        if (res.message) {
            document.getElementById('post-content').value = '';
            if (fileInput) fileInput.value = '';
            loadPosts(window.currentProfileId || null);
        }
    });
}

// Init
if (feedContainer) {
    // Check if we are on profile page
    const urlParams = new URLSearchParams(window.location.search);
    // Logic to distinguish profile page by URL or global var.
    // For now, index.html just calls loadPosts(). profile.html calls loadPosts(userId)
}

// Login

