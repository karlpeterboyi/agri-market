# Finance & Knowledge industry upgrade

## Finance
- Role `financier`: register institution, manage loans + insurance
- Loan types: equipment, input_credit, working_capital, warehouse_receipt, agribusiness, livestock
- Insurance types: crop, livestock, equipment, weather, multi, life
- UI: `/finance/institution`, `/finance/insurance`
- API: `/api/finance/*`, `/api/loan-products`, `/api/insurance-products`

## Knowledge
- Role `educator` (+ provider): institution + short courses
- Formats: self_paced, video, live, hybrid, downloadable
- UI: `/knowledge/educator`
- API: `/api/knowledge/*`, `/api/training-courses`

## Setup
```bash
php artisan migrate
php artisan tinker --execute="
App\Models\User::updateOrCreate(['email'=>'financier@example.com'],['name'=>'CRDB Agri','phone'=>'0755000099','role'=>'financier','password'=>bcrypt('password'),'status'=>'active']);
App\Models\User::updateOrCreate(['email'=>'educator@example.com'],['name'=>'SUA Extension','phone'=>'0755000088','role'=>'educator','password'=>bcrypt('password'),'status'=>'active']);
"
```
