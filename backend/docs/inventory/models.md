# Backend Model Registry

Legend

✅ Existing

🟡 Refactor Needed

🔴 Planned


## Core

✅ User

✅ Organisation

✅ OrganisationMember

✅ Role

✅ Permission

---

## Farm ERP

✅ Farm

✅ FarmWarehouse

✅ InventoryItem

✅ InventoryTransaction

✅ Crop

✅ CropCycle

✅ FarmActivity

---

## Finance

✅ Account

🔴 JournalEntry

🔴 Loan

---

## Weather

✅ WeatherStation

✅ WeatherObservation

✅ WeatherAlert

---

## Documents

✅ Document

---

## Audit

✅ AuditLog

---

## Workflow

✅ Workflow

✅ WorkflowStep

✅ WorkflowInstance

✅ Task

✅ TaskAssignment

✅ TaskComment

✅ TaskChecklist

---

## AI

✅ AIRecommendation

---

## Marketplace

🟡 Listing

🟡 Order

🟡 Cart

🟡 Payment

---

## Research

🔴 ResearchProject

🔴 Experiment

🔴 Dataset

Core User & Profile Models

​Models/User.php
​Models/FarmerProfile.php
​Models/BuyerProfile.php
​Models/ProviderProfile.php
​Models/Account.php

E-Commerce & Marketplace

​Models/Commodity.php
​Models/CommodityCategory.php
​Models/ProductListing.php
​Models/Offer.php
​Models/Order.php
​Models/OrderItem.php
​Models/ShoppingCart.php
​Models/CartItem.php
​Models/Wishlist.php
​Models/Payment.php

Logistics & Transport

​Models/Transporter.php
​Models/Vehicle.php
​Models/LogisticsRequest.php
​Models/TransportAssignment.php
​Models/DeliveryUpdate.php
​Models/Rating.php
​Wallet & Payments
​Models/Wallet.php
​Models/WalletTransaction.php
​Models/Withdrawal.php

Livestock Module

​Models/LivestockCategory.php
​Models/LivestockBreed.php
​Models/LivestockListing.php
​Models/LivestockImage.php

Machinery & Inputs

​Models/MachineryCategory.php
​Models/MachineryBrand.php
​Models/MachineryModel.php
​Models/MachineryListing.php
​Models/MachineryImage.php
​Models/MachineryReview.php
​Models/MachineryBooking.php
​Models/InputCategory.php
​Models/InputListing.php

Services Module

​Models/Service.php
​Models/ServiceCategory.php
​Models/ServiceRequest.php
​Models/ServiceQuote.php
​Models/ServiceBooking.php
​Models/ServiceReview.php
​Models/ServicePackage.php

Subscriptions

​Models/SubscriptionPlan.php
​Models/UserSubscription.php
​Models/SubscriptionPrice.php
​Models/PlanFeature.php

Financial & Loans

​Models/FinancialInstitutionCategory.php
​Models/FinancialInstitution.php
​Models/LoanProduct.php
​Models/LoanApplication.php
​Models/LoanApplicationDocument.php
​Models/LoanGuarantor.php
​Models/LoanCollateral.php
​Models/LoanRepayment.php
​Models/LoanDisbursement.php
​Models/CreditScore.php
​Models/InstitutionPreference.php
​Models/LoanWorkflowLog.php

Research & Extension Services

​Models/ResearchInstitution.php
​Models/Researcher.php
​Models/ResearchPublication.php
​Models/DemonstrationFarm.php
​Models/ExtensionOfficer.php
​Models/AdvisoryRequest.php
​Models/FarmVisit.php
​Models/ExtensionOfficerRating.php
​Models/FarmerAdvisory.php

Disease & Health Management

​Models/Disease.php
​Models/DiseaseReport.php
​Models/DiseaseDiagnosis.php
​Models/DiseaseVerification.php
​Models/DiseaseOutbreak.php

Weather & Agronomy

​Models/WeatherStation.php
​Models/WeatherObservation.php
​Models/WeatherAlert.php
​Models/Crop.php
​Models/CropVariety.php
​Models/CropCalendar.php
​Models/CropCalendarActivity.php
​Models/AgroEcologicalZone.php
​Models/CropGrowthStage.php
​Models/CropStageTask.php
​Models/CropRule.php
​Models/SoilProfile.php

Models/CropSuitability.php

​Farm Management & Inventory
​Models/Farm.php
​Models/FarmBoundary.php
​Models/FieldBlock.php
​Models/CropCycle.php
​Models/FarmActivity.php
​Models/ActivityAttachment.php
​Models/FarmWarehouse.php
​Models/InventoryItem.php
​Models/StockMovement.php

Organization & Auditing

​Models/Organisation.php
​Models/OrganisationMember.php
​Models/OrganisationInvitation.php
​Models/AuditLog.php
​Models/Document.php
​Models/Notification.php

Task & Workflow Management

​Models/Task.php
​Models/TaskAssignment.php
​Models/TaskComment.php
​Models/TaskChecklist.php
​Models/Workflow.php
​Models/WorkflowStep.php
​Models/WorkflowInstance.php
​Models/WorkflowSchedule.php
​Models/WorkflowReminder.php

AI Integration

​Models/AIRecommendation.php