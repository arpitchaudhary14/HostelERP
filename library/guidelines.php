<?php
session_start();
require_once "../db.php";
include("../header.php");
?>
<div class="container mt-5 page-fade-in">
    <div class="text-center mb-5 reveal">
        <img src="/WebTechProject/assets/images/Indexia_Logo.jpeg" height="80" class="mb-3 rounded-4 shadow">
        <h1 class="fw-bold text-gradient">Indexia Library Guidelines</h1>
        <p class="text-muted">Nurturing Minds, One Page at a Time.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4 reveal">
            <div class="glass-card-light h-100 p-4 text-center">
                <div class="stat-icon bg-primary-subtle text-primary mb-3 mx-auto">
                    <i class="bi bi-volume-mute h3"></i>
                </div>
                <h5 class="fw-bold">Silence Zone</h5>
                <p class="small text-muted">Maintain absolute silence. Use headphones for any audio requirements. Group discussions are only allowed in the 'Collaboration Wing'.</p>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="glass-card-light h-100 p-4 text-center">
                <div class="stat-icon bg-warning-subtle text-warning mb-3 mx-auto">
                    <i class="bi bi-cup-hot h3"></i>
                </div>
                <h5 class="fw-bold">No Food/Drinks</h5>
                <p class="small text-muted">Consumption of food and beverages (except water) is strictly prohibited inside the library to protect the books and maintain hygiene.</p>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="glass-card-light h-100 p-4 text-center">
                <div class="stat-icon bg-info-subtle text-info mb-3 mx-auto">
                    <i class="bi bi-journal-check h3"></i>
                </div>
                <h5 class="fw-bold">Borrowing Limits</h5>
                <p class="small text-muted">Students can borrow up to <strong>2 books</strong> at a time for a period of <strong>14 days</strong>. Renewals are allowed if there's no waiting list.</p>
            </div>
        </div>
        <div class="col-lg-8 reveal">
            <div class="glass-card-light p-4">
                <h5 class="fw-bold mb-4">Terms & Conditions</h5>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                        <div>
                            <strong>Identification:</strong> Every student must carry their Digital Library ID generated from the Indexia portal.
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                        <div>
                            <strong>Book Handling:</strong> Marking, underlining, or folding pages is strictly forbidden. Any damage will result in the full cost of the book being charged.
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                        <div>
                            <strong>Late Fines:</strong> A fine of <strong>₹5.00 per day</strong> will be automatically calculated after the due date.
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                        <div>
                            <strong>Lost Books:</strong> If a book is lost, the student must replace it with a new copy of the same edition or pay 150% of the current market price.
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-4 reveal">
            <div class="glass-card-light p-4 h-100">
                <h5 class="fw-bold mb-4">Library Hours</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Monday - Friday</span>
                    <span class="fw-bold text-primary">08:00 AM - 10:00 PM</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Saturday</span>
                    <span class="fw-bold text-primary">10:00 AM - 08:00 PM</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span>Sunday</span>
                    <span class="fw-bold text-danger">Closed</span>
                </div>
                <div class="p-3 bg-primary-subtle rounded-3 text-center">
                    <p class="small text-primary mb-0 fw-bold">Reading Room is open 24/7 for Exam Season!</p>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; }
</style>
<?php include("../footer.php"); ?>