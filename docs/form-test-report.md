# Form submissions: real WordPress end-to-end report

Run: 2026-10-13T20:51:14.929Z

Real WordPress with the theme zip installed. The browser submits the forms; WordPress sends each email through its own wp_mail (PHPMailer) to a real SMTP server that keeps the message.

## Booking form on the Contact Us page

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/contact-us/?dh_form=booking&dh_status=sent#form-booking`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `Test Patient <patient@example.com>`, Subject `[The Dental Hauz] Appointment request from Test Patient`

```
Appointment request
===================
First Name: Test
Last Name: Patient
Phone: 905 555 0100
Your Email: patient@example.com
Service You Would Like to Book: Dental Implants
Preferred Day: Monday
Preferred Time: Morning
```

## Booking form on the Home page

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/?dh_form=booking&dh_status=sent#form-booking`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `Test Patient <patient@example.com>`, Subject `[The Dental Hauz] Appointment request from Test Patient`

```
Appointment request
===================
First Name: Test
Last Name: Patient
Phone: 905 555 0100
Your Email: patient@example.com
Service You Would Like to Book: Dental Implants
Preferred Day: Monday
Preferred Time: Morning
```

## Medical history form

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `medical-history-form`, 61 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/medical-history-form/?dh_form=medical-history-form&dh_status=sent#form-medical-history-form`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `Sample full_name <patient@example.com>`, Subject `[The Dental Hauz] Medical history form from Sample full_name`

```
Medical history form
====================

ABOUT YOU
Full name: Sample full_name
Date of birth (day/month/year): 1990-05-17
Address (home): Sample address
Postal code: Sample postal
City: Sample city
Phone: 905 555 0100
Occupation: Sample occupation
Business phone: 905 555 0100
Cell phone: 905 555 0100
E-mail: patient@example.com
...
```

## Request for records

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `request-for-records`, 10 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/request-for-records/?dh_form=request-for-records&dh_status=sent#form-request-for-records`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `-`, Subject `[The Dental Hauz] Request for records from Sample patient_name`

```
Request for records
===================
Dr Name: Sample dr_name
Patient Name: Sample patient_name
Would you kindly forward the following records: Radiographs, Panorex
Message: Sample message from the end-to-end test.
All of the above will be kept indefinitely should you require them in the future. Thank you.: Yes
Date: 1990-05-17
```

## Dental treatment payment agreement

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `dental-treatment-payment-agreement`, 16 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/dental-treatment-payment-agreement/?dh_form=dental-treatment-payment-agreement&dh_status=sent#form-dental-treatment-payment-agreement`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `-`, Subject `[The Dental Hauz] Dental treatment payment agreement from Sample patient_name`

```
Dental treatment payment agreement
==================================
Patient Name: Sample patient_name
Treatment: Sample treatment
Appointment date: 1990-05-17
Primary Insurance Contribution: Sample primary_insurance
Secondary Insurance Contribution: Sample secondary_insurance
Patient Portion: Sample patient_portion
Deposit Due Prior to Appointment: Sample deposit
Remaining Balance Due at Appointment: Sample balance

ADDITIONAL TERMS

PATIENT ACKNOWLEDGMENT
...
```

## Payment agreement

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `payment-agreement`, 10 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/payment-agreement/?dh_form=payment-agreement&dh_status=sent#form-payment-agreement`
- SMTP envelope: from `wordpress@127.0.0.1` to `thedentalhauz@gmail.com`
- Email headers: To `thedentalhauz@gmail.com`, Reply-To `-`, Subject `[The Dental Hauz] Payment agreement from Sample patient_name`

```
Payment agreement
=================
Patient Name: Sample patient_name
Insurance coverage: Sample insurance

PAYMENT AGREEMENT TERMS

PATIENT ACKNOWLEDGMENT
Full Name: Sample ack_name
I acknowledge that I have read and understand the above payment agreement. I agree to the terms stated and accept financial responsibility for the services provided.: Yes
Patient Signature: Sample signature
Date: 1990-05-17
```

## Missing required fields (browser check bypassed)

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/contact-us/?dh_form=booking&dh_status=invalid#form-booking`
- Redirected back with an error message. No email was sent.

## Spam bot (hidden field filled)

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/contact-us/?dh_form=booking&dh_status=sent#form-booking`
- The bot is told it worked, but no email is sent and nothing is saved.

## Recipient changed in the Customizer (Send form submissions to)

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/contact-us/?dh_form=booking&dh_status=sent#form-booking`
- SMTP envelope: from `wordpress@127.0.0.1` to `frontdesk@example.test`
- Email headers: To `frontdesk@example.test`, Reply-To `Reroute <reroute@example.com>`, Subject `[The Dental Hauz] Appointment request from Reroute`

```
Appointment request
===================
First Name: Reroute
Last Name: -
Phone: 905 555 0111
Your Email: reroute@example.com
Service You Would Like to Book: Dental Cleaning
Preferred Day: Wednesday
Preferred Time: Afternoon
```

## Mail server down

- Browser sent: `POST http://127.0.0.1:8099/wp-admin/admin-post.php`
- Form id: `booking`, 11 fields
- WordPress answered: `302` redirect to `http://127.0.0.1:8099/contact-us/?dh_form=booking&dh_status=error#form-booking`
- The patient sees an error message and no email is sent, but the request is saved under Form entries in the dashboard (checked next).

## Dashboard "Form entries"

- Saved entries: 8

  - [The Dental Hauz] Appointment request from Offline
  - [The Dental Hauz] Appointment request from Reroute
  - [The Dental Hauz] Payment agreement from Sample patient_name
  - [The Dental Hauz] Dental treatment payment agreement from Sample patient_name
  - [The Dental Hauz] Request for records from Sample patient_name
  - [The Dental Hauz] Medical history form from Sample full_name
  - [The Dental Hauz] Appointment request from Test Patient
  - [The Dental Hauz] Appointment request from Test Patient

## PHP error log

- Entries not from the theme: 1
