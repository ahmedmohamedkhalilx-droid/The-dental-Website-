# Old site (thedentalhauz.com) to new theme: checklist

Built from the WordPress export `thedentalhauz.WordPress.2026-10-12.xml`. Every page keeps its address (slug), so links, Rank Math SEO titles and descriptions and Google's index carry over.

## Pages

| Old page | Address | Text strings checked |
|---|---|---|
| Home | `/` | 85 |
| About Us | `/about-us/` | 33 |
| Services | `/our-services/` | 41 |
| Comprehensive Oral Examination | `/comprehensive-oral-examination/` | 28 |
| Dental Cleaning | `/dental-cleaning/` | 28 |
| Tooth-Colored Restorations | `/tooth-colored-restorations/` | 28 |
| Crowns & Bridges | `/crowns-bridges/` | 28 |
| Dental Implants | `/dental-implants/` | 28 |
| Orthodontics & Clear Aligners | `/orthodontics-clear-aligners/` | 28 |
| Simple & Wisdom Tooth Extractions | `/simple-wisdom-tooth-extractions/` | 28 |
| Root Canal Therapy | `/root-canal-therapy/` | 28 |
| Kids Dentistry | `/kids-dentistry/` | 28 |
| Teeth whitening | `/teeth-whitening/` | 28 |
| Testimonial | `/testimonial/` | 8 |
| FAQ's | `/faqs/` | 26 |
| Contact Us | `/contact-us/` | 23 |
| Forms | `/forms/` | 6 |
| Medical History Form | `/medical-history-form/` | 72 |
| Request For Records | `/request-for-records/` | 15 |
| Dental Treatment Payment Agreement | `/dental-treatment-payment-agreement/` | 14 |
| Payment Agreement | `/payment-agreement/` | 9 |

**612 text strings** found on the 21 old pages were each looked for in the matching new page by `tests/theme/completeness.py`. Every one is present, except the 33 listed below, each with the reason.

## Differences from the old pages

**All pages**

- `Divider`: Page-builder widget label, not visible text on the old site

**Home**

- `Modern Anesthesic`: Spelling fixed to "Modern Anesthetic"

**Contact Us**

- `DENTISTS`: Banner breadcrumb carried the wrong page name; the new page shows a correct "Home / Contact Us" breadcrumb
- `01`: Decorative card numbers not carried over
- `03`: Decorative card numbers not carried over
- `04`: Decorative card numbers not carried over
- `8321 Kennedy Rd#22 , Markham, ON L3N 5N4`: Postal code corrected to L3R 5N4 (the old site used L3R 5N4 on its forms and map, L3N 5N4 here and in its header); address now comes from Customizer > Clinic details
- `Monday - Friday: 10AM - 6PM`: Same hours, reformatted; now come from Customizer > Clinic details
- `Saturday: 9:00AM – 6:00PM`: Same hours, reformatted; now come from Customizer > Clinic details
- `The Dental Hauz 8321 Kennedy Rd #22, Markham, ON L3R 5N4, Canada`: Used as the address of the embedded Google Map (checked separately)

**Testimonial**

- `Testimonials`: Banner title; the new page uses the page title "Testimonial"
- `TESTIMONIALS`: Banner breadcrumb; replaced by a real breadcrumb

**Services**

- `DENTISTS`: Banner breadcrumb carried the wrong page name; replaced by a real breadcrumb

**Medical History Form**

- `8321 KENNEDY RD. UNIT # 22. MARKHAM, L3R 5N4`: Same address, now from Customizer > Clinic details
- `905.470.4289`: Same phone number, now from Customizer > Clinic details
- `MEDICAL ALERT:`: Heading with no content under it on the old page; left out
- `IN CASE OF EMERGENCY, WE SHOULD NOTIFY:`: Same heading without the trailing colon
- `NAMERELATIONSHIP`: Label typo; now "Relationship"
- `Not Sure/Maybe`: Answer option spelled "Not sure / Maybe"
- `Do you have any allergies? If you answered yes, please list using the categories below: a) medications b) late...`: Two questions had been merged into one label; split into the allergies question and the medications question, each with its own "if yes, please list" box
- `I Have you ever been hospitalized for any illnesses or operations?`: Typo; now "Have you ever been hospitalized..."
- `chesr pain,angaina`: Typo; now "chest pain, angina"
- `eheumatic fever`: Typo; now "rheumatic fever"
- `thyroid dusease`: Typo; now "thyroid disease"
- `steroid theraypy`: Typo; now "steroid therapy"

**Request For Records**

- `8321 KENNEDY RD. UNIT # 22. MARKHAM, L3R 5N4`: Same address, now from Customizer > Clinic details
- `905.470.4289`: Same phone number, now from Customizer > Clinic details
- `Pataint Name, has/have recently become a patient/patients in my practice`: Typo; now "Patient Name" with the "has/have recently become a patient/patients in my practice" note under it

**Dental Treatment Payment Agreement**

- `I, acknowledge that I have read and understand the above payment agreement. I agree to the terms stated and ac...`: Stray comma removed; a "Full name of patient" box added so the patient can state who is acknowledging
- `Dental Office Representative Signature`: Image-upload signature replaced by a typed "Dental Office Representative" name
- `8321 KENNEDY RD. UNIT # 22. MARKHAM, L3R 5N4 905.470.4289`: Was a mistaken radio button holding the address; shown as a plain address line from Customizer > Clinic details

**Payment Agreement**

- `I, _ acknowledge that I have read and understand the above payment agreement. I agree to the terms stated and ...`: Blank in the sentence removed; the patient's "Full Name" box sits above it
- `8321 KENNEDY RD. UNIT # 22. MARKHAM, L3R 5N4 905.470.4289`: Was a mistaken radio button holding the address; shown as a plain address line from Customizer > Clinic details

## Left out on purpose

- **"Our Accomplishments" counters** (About and FAQ pages): 875+ Happy Patients, 115+ Experts Doctors, 95+ Hospital Room, 79+ Award Winner. They look like leftovers from the template (a single clinic with 95 "hospital rooms"), so they are off by default. Switch them on under Appearance > Customize > Clinic details if the numbers are real.
- **Decorative images**: background patterns and banner textures (overly.png, shap-bg.png, main-banner-11.png, banner-bg.jpg, icon-1.png and similar). Every photograph used on the old pages is used on the new ones.
- **"Powered By Top Tech Digital Ai" footer credit**: a web vendor credit, not clinic information. Easy to add back to `footer.php`.
- **Second email copy of every form**: the old forms also emailed each submission to an address at the previous web vendor ("usasmartwebsite"). Submissions now go only to the clinic address. Medical history forms contain personal health information, so check you want any copy sent elsewhere.
- **Unused Dentisto demo templates** inside Elementor (lorem ipsum testimonials, "King Street Melbourne" header, "Neurology / Oncology" footer links). Not live content.
- **Medical history "MEDICAL ALERT:" heading**: it had nothing under it.

## Things to check

- Postal code: the old site shows **L3R 5N4** on its forms and map and **L3N 5N4** on its contact page and header. The new site uses L3R 5N4. Change it under Appearance > Customize > Clinic details if that is wrong.
- Booking form "Preferred Time": the last option was labelled "Saturday" on the old form; it is now "No Preference" (its stored value).
- Medical history: obvious typos fixed, the merged allergies/medications question split in two, a duplicated heart/blood-pressure question removed, "If yes" boxes made optional so a "No" answer can be submitted.
- File uploads on the old forms (records "Message", signatures) are replaced by typed names, because email forms should not accept file uploads from the public.
- Hours: Monday to Friday 10 AM to 6 PM, Saturday 9 AM to 6 PM, Sunday closed (from the old Contact page).
- Reviews come from the **Trustindex** plugin (`[trustindex no-registration=google]`). Keep that plugin active.
