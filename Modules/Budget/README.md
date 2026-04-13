# Budget module

Autonomous Budget module for WebTools Manager.

## Included in this version
- Monthly budget overview
- Budget editing for income, expense, details and objectives
- Reports by category
- Reports by subcategory
- Annual analysis dashboard
- Charts with Chart.js

## Routes
- `/budget`
- `/budget/reports/category`
- `/budget/reports/subcategory`
- `/budget/reports/annual`

## Notes
- Keeps compatibility with the existing `wt_budget_*` tables.
- Uses the global `layouts.app` layout.
- Reporting pages reuse the same auth middleware and module provider structure.
