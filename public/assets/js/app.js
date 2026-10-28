// Hiển thị thông báo trong form (dùng textContent để tránh XSS)
function showAlert(box, type, message, errors) {
  if (!box) return;
  box.className = "form-alert alert alert-" + type;
  box.replaceChildren(document.createTextNode(message || ""));
  if (errors) {
    const ul = document.createElement("ul");
    ul.className = "mb-0 mt-2";
    Object.values(errors).forEach((msg) => {
      const li = document.createElement("li");
      li.textContent = msg;
      ul.appendChild(li);
    });
    box.appendChild(ul);
  }
}

// Mọi form có thuộc tính data-ajax sẽ được gửi bằng fetch
document.addEventListener("submit", async (e) => {
  const form = e.target.closest("form[data-ajax]");
  if (!form) return;
  e.preventDefault();

  const box = form.querySelector(".form-alert");
  const btn = form.querySelector('[type="submit"], button:not([type])');

  // Kiểm tra nhanh phía client (server vẫn kiểm tra lại)
  const pw = form.querySelector('[name="matkhau"]');
  const cf = form.querySelector('[name="xacnhan"]');
  if (pw && cf && pw.value !== cf.value) {
    showAlert(box, "danger", "Mật khẩu xác nhận không khớp.");
    return;
  }

  if (btn) btn.disabled = true;
  try {
    const res = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    const raw = await res.text();
    let data;
    try {
      data = JSON.parse(raw);
    } catch (e) {
      showAlert(
        box,
        "danger",
        "Máy chủ trả về phản hồi không hợp lệ (HTTP " +
          res.status +
          "): " +
          raw.slice(0, 200),
      );
      return;
    }
    if (data.success) {
      showAlert(box, "success", data.message);
      if (data.redirect)
        setTimeout(() => {
          location.href = data.redirect;
        }, 700);
    } else {
      showAlert(box, "danger", data.message, data.errors);
      if (data.redirect)
        setTimeout(() => {
          location.href = data.redirect;
        }, 1200);
    }
  } catch (err) {
    showAlert(box, "danger", "Không thể kết nối máy chủ, vui lòng thử lại.");
  } finally {
    if (btn) btn.disabled = false;
  }
});
