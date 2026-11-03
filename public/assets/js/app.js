// public/assets/js/app.js
// 1) Toast + Alert   2) Gửi form bằng AJAX (fetch)   3) Các tiện ích nhỏ của từng trang
(function () {
  'use strict';

  // ---------- Toast ----------
  // type: success | danger | warning | info
  window.showToast = function (message, type) {
    type = type || 'success';
    var box = document.getElementById('toast-container');
    if (!box || !message) return;

    var icons = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-' + type + ' border-0';
    el.setAttribute('role', type === 'danger' ? 'alert' : 'status');

    var wrap = document.createElement('div');
    wrap.className = 'd-flex';
    var body = document.createElement('div');
    body.className = 'toast-body';
    var icon = document.createElement('i');
    icon.className = 'bi ' + (icons[type] || icons.info) + ' me-2';
    body.appendChild(icon);
    body.appendChild(document.createTextNode(message));   // textContent: an toàn trước XSS
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close ' + (type === 'warning' ? '' : 'btn-close-white ') + 'me-2 m-auto';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', 'Đóng');
    wrap.appendChild(body);
    wrap.appendChild(close);
    el.appendChild(wrap);
    box.appendChild(el);

    var t = new bootstrap.Toast(el, { delay: type === 'danger' ? 6000 : 4000 });
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    t.show();
  };

  // ---------- Alert trong form (liệt kê lỗi) ----------
  function alertBox(form) {
    var box = form.querySelector('[data-alert]');
    if (!box) {
      box = document.createElement('div');
      box.setAttribute('data-alert', '');
      form.insertBefore(box, form.firstChild);
    }
    return box;
  }
  function showAlert(form, messages) {
    var box = alertBox(form);
    box.textContent = '';
    if (!messages.length) return;
    var div = document.createElement('div');
    div.className = 'alert alert-danger';
    div.setAttribute('role', 'alert');
    if (messages.length === 1) {
      div.textContent = messages[0];
    } else {
      var ul = document.createElement('ul');
      ul.className = 'mb-0 ps-3';
      messages.forEach(function (m) {
        var li = document.createElement('li');
        li.textContent = m;
        ul.appendChild(li);
      });
      div.appendChild(ul);
    }
    box.appendChild(div);
    div.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
  function clearAlert(form) {
    var box = form.querySelector('[data-alert]');
    if (box) box.textContent = '';
  }

  // ---------- Form AJAX: <form data-ajax method="post" action="..."> ----------
  // Máy chủ trả JSON: { ok, message, errors[], redirect? }
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('form[data-ajax]')) return;
    e.preventDefault();

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      form.reportValidity();
      return;
    }

    var btn = form.querySelector('[type="submit"]');
    var original = btn ? btn.innerHTML : '';
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Đang xử lý...';
    }
    clearAlert(form);

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function (res) {
        return res.json().then(function (data) { return { res: res, data: data }; });
      })
      .then(function (r) {
        var data = r.data;
        if (data.ok) {
          showToast(data.message, 'success');
          if (form.hasAttribute('data-reset')) {
            form.reset();
            form.classList.remove('was-validated');
          }
          form.dispatchEvent(new CustomEvent('ajax:success', { bubbles: true, detail: data }));
          if (data.redirect) {
            setTimeout(function () { window.location.href = data.redirect; }, 900);
            return;   // giữ nút ở trạng thái "đang xử lý" trong lúc chuyển trang
          }
        } else {
          var msgs = (data.errors && data.errors.length) ? data.errors : [data.message || 'Thao tác thất bại.'];
          showAlert(form, msgs);
          showToast(data.message || 'Thao tác thất bại.', r.res.status === 409 ? 'warning' : 'danger');
          form.dispatchEvent(new CustomEvent('ajax:error', { bubbles: true, detail: data }));
          if (r.res.status === 401 && data.redirect) {
            setTimeout(function () { window.location.href = data.redirect; }, 1200);
          }
        }
        resetButton();
      })
      .catch(function () {
        var msg = 'Không thể kết nối máy chủ. Vui lòng kiểm tra mạng và thử lại.';
        showAlert(form, [msg]);
        showToast(msg, 'danger');
        resetButton();
      });

    function resetButton() {
      if (btn) { btn.disabled = false; btn.innerHTML = original; }
    }
  });

  // ---------- Khởi tạo khi tải trang ----------
  document.addEventListener('DOMContentLoaded', function () {
    // Flash từ máy chủ (sau redirect) -> Toast
    var flash = document.getElementById('flash-data');
    if (flash) {
      if (flash.dataset.success) showToast(flash.dataset.success, 'success');
      if (flash.dataset.error) showToast(flash.dataset.error, 'danger');
    }

    // Trang đăng ký: chỉ hiện ô "Kỹ năng" khi chọn vai trò Người làm tự do
    var skills = document.getElementById('skills-group');
    var roles = document.querySelectorAll('input[name="vaitro"]');
    if (skills && roles.length) {
      var toggle = function () {
        var fl = document.getElementById('role-fl');
        skills.classList.toggle('d-none', !(fl && fl.checked));
      };
      roles.forEach(function (r) { r.addEventListener('change', toggle); });
      toggle();
    }

    // Ô tiền: định dạng 15000000 -> 15.000.000 khi gõ (server tự bỏ dấu chấm)
    document.querySelectorAll('input[data-money]').forEach(function (input) {
      input.addEventListener('input', function () {
        var digits = input.value.replace(/\D/g, '').slice(0, 12);
        input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      });
    });

    // Bộ đếm ký tự: <textarea data-counter="#id">
    document.querySelectorAll('[data-counter]').forEach(function (field) {
      var out = document.querySelector(field.dataset.counter);
      if (!out) return;
      var update = function () { out.textContent = field.value.length + '/' + field.maxLength; };
      field.addEventListener('input', update);
      update();
    });

    // Dashboard: số liệu đếm từ 0 lên giá trị thật (bỏ qua nếu người dùng chọn giảm chuyển động)
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce) {
      document.querySelectorAll('.wl-stat-num').forEach(function (el) {
        var target = parseInt(el.textContent, 10);
        if (isNaN(target) || target <= 0) return;
        var start = null, dur = 700;
        el.textContent = '0';
        (function step(ts) {
          if (start === null) start = ts;
          var p = Math.min((ts - start) / dur, 1);
          el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));   // ease-out
          if (p < 1) requestAnimationFrame(step);
        })(performance.now());
      });
    }

    // Modal gửi đề xuất: điền thông tin dự án được bấm
    var modal = document.getElementById('proposalModal');
    if (modal) {
      modal.addEventListener('show.bs.modal', function (ev) {
        var b = ev.relatedTarget;
        if (!b) return;
        var form = modal.querySelector('form');
        form.querySelector('[name="id_duan"]').value = b.dataset.projectId;
        modal.querySelector('[data-project-title]').textContent = b.dataset.projectTitle;
        modal.querySelector('[data-project-budget]').textContent = b.dataset.projectBudget;
        clearAlert(form);
        form.classList.remove('was-validated');
      });
    }
  });

  // ---------- Phản hồi sau khi AJAX thành công ----------
  document.addEventListener('ajax:success', function (ev) {
    var form = ev.target, data = ev.detail || {};

    // Gửi đề xuất: đóng modal, khóa nút, tăng bộ đếm ngay trên danh sách
    if (form.id === 'proposal-form') {
      var modal = document.getElementById('proposalModal');
      var inst = modal && bootstrap.Modal.getInstance(modal);
      if (inst) inst.hide();
      markProposalSent(data.project_id, data.count);
    }

    // Hồ sơ: cập nhật tên trên thanh menu
    if (form.id === 'profile-form' && data.hoten) {
      document.querySelectorAll('[data-user-name]').forEach(function (n) { n.textContent = data.hoten; });
      document.querySelectorAll('[data-user-initial]').forEach(function (n) { n.textContent = data.hoten.charAt(0).toUpperCase(); });
    }
  });

  // Nếu server báo "đã gửi rồi" (409) thì cũng đồng bộ lại nút trên danh sách
  document.addEventListener('ajax:error', function (ev) {
    var d = ev.detail || {};
    if (ev.target.id === 'proposal-form' && d.project_id) {
      var modal = document.getElementById('proposalModal');
      var inst = modal && bootstrap.Modal.getInstance(modal);
      if (inst) inst.hide();
      markProposalSent(d.project_id, null);
    }
  });

  function markProposalSent(projectId, count) {
    document.querySelectorAll('[data-project-id="' + projectId + '"]').forEach(function (btn) {
      if (!btn.hasAttribute('data-bs-toggle')) return;
      var done = document.createElement('button');
      done.type = 'button';
      done.disabled = true;
      done.className = 'btn btn-success btn-sm wl-pop';
      done.innerHTML = '<i class="bi bi-check2"></i> Đã gửi đề xuất';
      btn.replaceWith(done);
    });
    if (count !== null && count !== undefined) {
      document.querySelectorAll('[data-proposal-count="' + projectId + '"]').forEach(function (n) {
        n.textContent = count;
        n.classList.remove('wl-pop');
        void n.offsetWidth;                 // khởi động lại hiệu ứng
        n.classList.add('wl-pop');
        n.style.display = 'inline-block';
      });
    }
  }
})();
