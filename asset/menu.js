document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("menuToggle");
  const nav = document.getElementById("globalNav");

  if (!toggle || !nav) {
    console.error("menuToggle または globalNav が見つかりません。");
    return;
  }

  // ===== オーバーレイ作成 =====
  const overlay = document.createElement("div");
  overlay.classList.add("overlay");
  document.body.appendChild(overlay);

  // ===== サブメニュー初期化＆ユーティリティ =====
  const submenuItems = Array.from(nav.querySelectorAll(".has-submenu"));
  submenuItems.forEach((item) => {
    const list = item.querySelector(".submenu");
    if (!list) return;
    // 初期状態は閉じる
    list.style.display = "block"; // アニメを効かせるために block 管理
    list.style.maxHeight = "0px";
    list.style.overflow = "hidden";
    list.style.opacity = "0";
    list.style.transform = "translateY(-4px)";
    list.style.transition =
      "max-height .35s ease, opacity .25s ease, transform .35s ease";

    // 開いた後の高さ自動追従：open後にmax-heightをautoへ
    list.addEventListener("transitionend", (e) => {
      if (e.propertyName === "max-height" && item.classList.contains("open")) {
        list.style.maxHeight = "none"; // コンテンツ変化にも追従
      }
    });
  });

  const openSubmenu = (item) => {
    const list = item.querySelector(".submenu");
    if (!list) return;
    // いったん max-height を具体値に
    list.style.maxHeight = list.scrollHeight + "px";
    list.style.opacity = "1";
    list.style.transform = "translateY(0)";
    item.classList.add("open");
  };

  const closeSubmenu = (item) => {
    const list = item.querySelector(".submenu");
    if (!list) return;
    // auto → 具体値 に戻してから 0 にするとスムーズ
    if (list.style.maxHeight === "none") {
      list.style.maxHeight = list.scrollHeight + "px";
    }
    // リフロー強制
    void list.offsetHeight;
    item.classList.remove("open");
    list.style.opacity = "0";
    list.style.transform = "translateY(-4px)";
    list.style.maxHeight = "0px";
  };

  const closeAllSubmenus = () => {
    submenuItems.forEach(closeSubmenu);
  };

  // ===== メニュー開閉 =====
  const closeMenu = () => {
    toggle.classList.remove("active");
    nav.classList.remove("open");
    overlay.classList.remove("show");
    // メニューを閉じたらサブメニューも畳む
    closeAllSubmenus();
  };

  toggle.addEventListener("click", () => {
    const willOpen = !nav.classList.contains("open");
    toggle.classList.toggle("active");
    nav.classList.toggle("open");
    overlay.classList.toggle("show");
    if (!willOpen) {
      // 閉じるときはサブメニューもリセット
      closeAllSubmenus();
    }
  });

  overlay.addEventListener("click", closeMenu);

  // メニュー内リンククリックで閉じる
  nav.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeMenu);
  });

  // ===== サブメニュー開閉（トグル）=====
  nav.querySelectorAll(".submenu-toggle").forEach((button) => {
    button.addEventListener("click", (e) => {
      const parent = e.currentTarget.closest(".has-submenu");
      if (!parent) return;
      const isOpen = parent.classList.contains("open");
      if (isOpen) {
        closeSubmenu(parent);
      } else {
        // 他を閉じたい場合は次の1行を有効化：
        // closeAllSubmenus();
        openSubmenu(parent);
      }
    });
  });
});
