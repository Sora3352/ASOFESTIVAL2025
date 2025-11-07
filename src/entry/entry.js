// =============================
// ASO FESTIVAL 2025 ENTRY SCRIPT
// Author: 井上遥喜
// =============================

document.addEventListener("DOMContentLoaded", () => {
  const projectSelect = document.querySelector("#project");
  const rulesSection = document.querySelector("#rules-section");
  const secondPlayer = document.querySelector("#second-player");
  const form = document.querySelector("#entryForm");

  // ローカルストレージ初期化（毎回リセット）
  localStorage.removeItem("aso_rules_opened");

  // 参加形態を挿入する場所（参加企画の直後に配置）
  projectSelect.insertAdjacentHTML(
    "afterend",
    `<div id="participation-wrapper"></div>`
  );
  const participationWrapper = document.querySelector("#participation-wrapper");

  projectSelect.addEventListener("change", handleProjectChange);

  function handleProjectChange() {
    const project = projectSelect.value;
    rulesSection.innerHTML = "";
    secondPlayer.style.display = "none";
    secondPlayer.innerHTML = "";
    participationWrapper.innerHTML = "";

    // 🟣 麻生祭大会出場規約（読むまでチェック無効）
    rulesSection.innerHTML += `
      <label>
        <input type="checkbox" id="rule_asofes" name="agreement" required disabled>
        <a href="rules.html" target="_blank" id="openAsofesRules">麻生祭大会出場規約</a>に同意します（必須）
      </label>
    `;

    // 🟡 スマブラ企画
    if (project === "sumabura") {
      addParticipationType();
      rulesSection.innerHTML += `
        <label>
          <input type="checkbox" id="rule_nintendo" name="nintendo_agreement" required>
          <a href="https://www.nintendo.co.jp/tournament_guideline/rules.html" target="_blank">任天堂大会規約</a>に同意します（必須）
        </label>
        <label>
          <input type="checkbox" id="rule_pre" name="preliminary" required>
          麻生祭当日と別日程にて予選会を行います。（必須）
        </label>
      `;
    }
    // 🟠 カラオケ企画
    else if (project === "karaoke") {
      rulesSection.innerHTML += `
        <label>
          <input type="checkbox" id="rule_pre" name="preliminary" required>
          麻生祭当日と別日程にて予選会を行います。（必須）
        </label>
      `;
    }

    // 🔹 ページ読み込み時に既読フラグを確認（規約チェックを有効化）
    const viewed = localStorage.getItem("aso_rules_opened");
    const ruleCheckbox = document.querySelector("#rule_asofes");
    if (viewed && ruleCheckbox) ruleCheckbox.disabled = false;
  }

  // ✅ 「麻生祭大会出場規約」クリック時に新タブで開き、既読フラグを保存
  document.addEventListener("click", (e) => {
    if (e.target && e.target.id === "openAsofesRules") {
      window.open("rules.html", "_blank");
      localStorage.setItem("aso_rules_opened", "1");
      const ruleCheckbox = document.querySelector("#rule_asofes");
      if (ruleCheckbox) ruleCheckbox.disabled = false;
    }
  });

  // === スマブラ時：参加形態セレクト追加 ===
  function addParticipationType() {
    participationWrapper.innerHTML = `
      <label for="participation_type">参加形態</label>
      <select id="participation_type" name="participation_type" required>
        <option value="">選択してください</option>
        <option value="1vs1">1vs1</option>
        <option value="2vs2">2vs2</option>
      </select>
    `;
    const participationType = document.querySelector("#participation_type");
    participationType.addEventListener("change", handleParticipationType);
  }

  // === 2vs2選択時：2人目の入力欄を追加 ===
  function handleParticipationType() {
    const type = this.value;
    if (type === "2vs2") {
      secondPlayer.style.display = "block";
      secondPlayer.innerHTML = `
        <h3>2人目の出場者情報</h3>
        <label>学籍番号</label>
        <input type="text" id="second_student_id" name="second_student_id" placeholder="例: 2501235" required>

        <label>メールアドレス</label>
        <input type="email" id="second_email" name="second_email" readonly placeholder="自動生成されます" required>

        <label>クラス</label>
        <select id="second_class" name="second_class" required></select>

        <label>名前</label>
        <input type="text" id="second_name" name="second_name" required>
      `;

      // クラスリストを読み込み
      fetch("class_options.php")
        .then((res) => res.text())
        .then((html) => {
          const select = document.querySelector("#second_class");
          select.innerHTML =
            `<option value="">選択してください</option>` + html;
        });

      // 学籍番号 → メール自動生成
      const idField = document.querySelector("#second_student_id");
      const mailField = document.querySelector("#second_email");
      idField.addEventListener("input", () => {
        const id = idField.value.replace(/[^0-9]/g, "").slice(0, 7);
        idField.value = id;
        mailField.value = id ? `${id}@s.asojuku.ac.jp` : "";
      });
    } else {
      secondPlayer.style.display = "none";
      secondPlayer.innerHTML = "";
    }
  }

  // === 送信時：必須チェック ===
  form.addEventListener("submit", (e) => {
    const project = projectSelect.value;

    const ruleAsofes = document.querySelector("#rule_asofes");
    if (!ruleAsofes?.checked) {
      e.preventDefault();
      alert("麻生祭大会出場規約への同意が必要です。");
      return;
    }

    if (project === "sumabura") {
      const nintendo = document.querySelector("#rule_nintendo");
      const pre = document.querySelector("#rule_pre");

      if (!nintendo?.checked || !pre?.checked) {
        e.preventDefault();
        alert("スマブラ参加には任天堂規約および予選会への同意が必要です。");
      }
    } else if (project === "karaoke") {
      const pre = document.querySelector("#rule_pre");
      if (!pre?.checked) {
        e.preventDefault();
        alert("カラオケ参加には予選会への同意が必要です。");
      }
    }
  });
});
