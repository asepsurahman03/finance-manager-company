const transactionForm = document.getElementById("transaction-form");
const transactionList = document.getElementById("transaction-list");
const totalIncomeElement = document.getElementById("total-income");
const totalExpenseElement = document.getElementById("total-expense");
const totalBalanceElement = document.getElementById("total-balance");
const currentMonthElement = document.getElementById("current-month");
const prevMonthButton = document.getElementById("prev-month");
const nextMonthButton = document.getElementById("next-month");
const dailySummaryList = document.getElementById("daily-summary-list");

let transactions = {
  "2024-12": [],
};

let currentDate = new Date();

function formatMonth(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
}

function displayMonth(date) {
  const months = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember",
  ];
  return `${months[date.getMonth()]} ${date.getFullYear()}`;
}

function formatDateTime(date) {
  const options = { day: "2-digit", month: "long", year: "numeric", hour: "2-digit", minute: "2-digit" };
  return date.toLocaleDateString("id-ID", options);
}

function formatDate(date) {
  const options = { day: "2-digit", month: "long", year: "numeric" };
  return date.toLocaleDateString("id-ID", options);
}

function updateMonth() {
  const currentMonthKey = formatMonth(currentDate);
  currentMonthElement.textContent = displayMonth(currentDate);

  if (!transactions[currentMonthKey]) {
    transactions[currentMonthKey] = [];
  }

  renderTransactions();
  updateSummary();
  renderDailySummary();
}

function updateSummary() {
  const currentMonthKey = formatMonth(currentDate);
  const monthTransactions = transactions[currentMonthKey];

  const totalIncome = monthTransactions
    .filter(txn => txn.amount > 0)
    .reduce((sum, txn) => sum + txn.amount, 0);
  const totalExpense = monthTransactions
    .filter(txn => txn.amount < 0)
    .reduce((sum, txn) => sum + Math.abs(txn.amount), 0);
  const totalBalance = totalIncome - totalExpense;

  totalIncomeElement.textContent = `Rp${totalIncome.toLocaleString()}`;
  totalExpenseElement.textContent = `Rp${totalExpense.toLocaleString()}`;
  totalBalanceElement.textContent = `Rp${totalBalance.toLocaleString()}`;
}

function calculateDailySummary() {
  const currentMonthKey = formatMonth(currentDate);
  const monthTransactions = transactions[currentMonthKey];

  const dailySummary = {};

  monthTransactions.forEach(({ dateTime, amount }) => {
    const date = formatDate(new Date(dateTime));
    if (!dailySummary[date]) {
      dailySummary[date] = { income: 0, expense: 0 };
    }
    if (amount > 0) {
      dailySummary[date].income += amount;
    } else {
      dailySummary[date].expense += Math.abs(amount);
    }
  });

  return dailySummary;
}

function renderDailySummary() {
  const dailySummary = calculateDailySummary();
  dailySummaryList.innerHTML = "";

  for (const [date, { income, expense }] of Object.entries(dailySummary)) {
    const summaryItem = document.createElement("div");
    summaryItem.className = "mb-4 p-4 border border-gray-300 rounded-lg bg-white shadow";

    summaryItem.innerHTML = `
      <h3 class="text-lg font-bold text-gray-700">${date}</h3>
      <p class="text-green-600">Pendapatan: Rp${income.toLocaleString()}</p>
      <p class="text-red-600">Pengeluaran: Rp${expense.toLocaleString()}</p>
    `;

    dailySummaryList.appendChild(summaryItem);
  }
}

function addTransaction(dateTime, description, amount) {
  const currentMonthKey = formatMonth(currentDate);
  const transaction = {
    id: Date.now(),
    dateTime,
    description,
    amount,
  };
  transactions[currentMonthKey].push(transaction);
  renderTransactions();
  updateSummary();
  renderDailySummary();
}

function renderTransactions() {
  const currentMonthKey = formatMonth(currentDate);
  const monthTransactions = transactions[currentMonthKey];
  transactionList.innerHTML = "";

  monthTransactions.forEach(txn => {
    const li = document.createElement("li");
    li.classList.add("bg-white", "shadow-md", "rounded-lg", "p-4", "flex", "justify-between", "items-start");
    li.innerHTML = `
      <div>
        <p class="font-bold">${txn.description}</p>
        <p class="text-gray-600 text-sm">${formatDateTime(new Date(txn.dateTime))}</p>
      </div>
      <div>
        <span class="text-lg ${txn.amount > 0 ? "text-green-500" : "text-red-500"}">
          Rp${txn.amount.toLocaleString()}
        </span>
        <button class="text-red-500 hover:text-red-700 ml-4" onclick="removeTransaction(${txn.id})">Hapus</button>
      </div>
    `;
    transactionList.appendChild(li);
  });
}

function removeTransaction(id) {
  const currentMonthKey = formatMonth(currentDate);
  transactions[currentMonthKey] = transactions[currentMonthKey].filter(txn => txn.id !== id);
  renderTransactions();
  updateSummary();
  renderDailySummary();
}

prevMonthButton.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  updateMonth();
});

nextMonthButton.addEventListener("click", () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  updateMonth();
});

transactionForm.addEventListener("submit", function (e) {
  e.preventDefault();
  const dateTime = document.getElementById("datetime").value;
  const description = document.getElementById("description").value;
  const amount = parseFloat(document.getElementById("amount").value);

  if (dateTime && description && !isNaN(amount)) {
    addTransaction(dateTime, description, amount);
    transactionForm.reset();
  } else {
    alert("Isi semua data dengan benar!");
  }
});

updateMonth();
