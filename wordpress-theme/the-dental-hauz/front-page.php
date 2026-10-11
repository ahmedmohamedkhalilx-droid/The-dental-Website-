<?php
/**
 * Front page: the full single-page home.
 *
 * @package the-dental-hauz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
  <!-- Hero -->
  <section class="hero">
    <img class="hero-mark" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/mark-dark.png' ) ); ?>" alt="" aria-hidden="true">
    <div class="wrap">
      <div class="hero-copy">
        <p class="eyebrow">Oral surgery &amp; modern dentistry</p>
        <h1>
          <span class="line">Your smile.</span>
          <span class="line">Your experience.</span>
          <span class="line hauz">Your Hauz.</span>
        </h1>
        <p class="hero-lead">Expert implant, surgical and cosmetic care in a clinic designed to feel like home. Calm rooms, clear plans and a team that takes the time to explain every step.</p>
        <div class="hero-actions">
          <a class="btn" href="#book">Book a Consultation</a>
          <a class="btn btn--ghost" href="#services">Explore Treatments</a>
        </div>
      </div>
      <div class="hero-media">
        <div class="frame"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/craft.jpg' ) ); ?>" alt="A dentist working carefully with a fine instrument" width="736" height="1308"></div>
        <div class="hero-badge">
          <strong>Comfort-first care</strong>
          <span>Local anaesthesia and sedation options for every surgical visit.</span>
        </div>
      </div>
    </div>
  </section>

  <div class="strip">
    <ul class="wrap">
      <li>Dental Implants</li>
      <li>Wisdom Teeth</li>
      <li>Clear Aligners</li>
      <li>Veneers</li>
      <li>Whitening</li>
    </ul>
  </div>

  <!-- About -->
  <section class="section about" id="about">
    <div class="wrap">
      <div class="about-media">
        <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/scrubs.jpg' ) ); ?>" alt="A Dental Hauz clinician in green scrubs with the embroidered logo" width="730" height="917">
      </div>
      <div class="about-copy">
        <p class="eyebrow">Welcome to the Hauz</p>
        <h2>Dentistry that feels like coming home</h2>
        <p>The Dental Hauz brings surgical precision and everyday dentistry under one roof. From a single implant to a full smile makeover, your treatment is planned, performed and followed up by the same team, so you never have to start your story again.</p>
        <p>We built the clinic around how a visit should feel: unhurried, quiet and honest about options and costs before anything begins.</p>
        <div class="pillars">
          <div><h3>Precise</h3><p>Digital scans and 3D imaging guide every surgical plan.</p></div>
          <div><h3>Gentle</h3><p>Pain control and sedation tailored to you.</p></div>
          <div><h3>Personal</h3><p>One team from first consultation to final check-up.</p></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section class="section services" id="services">
    <div class="wrap">
      <div class="section-head">
        <p class="eyebrow">Treatments</p>
        <h2>Complete care, from surgery to the finishing touches</h2>
        <p>Every treatment starts with a consultation and a written plan, so you know what will happen, how long it takes and what it costs.</p>
      </div>
      <div class="service-grid">
        <a class="service" href="#implants">
          <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M14 8c3-2 7-2 10 0 3-2 7-2 10 0 3 3 3 8 1 12H13c-2-4-2-9 1-12Z"/><path d="M18 24h12M19 29h10M20 34h8M21 39h6M24 20v23"/></svg>
          <h3>Dental Implants</h3>
          <p>Permanent, natural-looking replacements for one tooth, several teeth or a full arch, placed with guided surgery.</p>
          <span class="more">Learn more</span>
        </a>
        <a class="service" href="#book">
          <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M15 10c3-2 6-2 9 0 3-2 6-2 9 0 4 4 2 12 0 17-1 4-1 13-4 13-2 0-2-8-5-8s-3 8-5 8c-3 0-3-9-4-13-2-5-4-13 0-17Z"/><path d="M8 18l5 2M40 18l-5 2"/></svg>
          <h3>Wisdom Teeth</h3>
          <p>Safe removal of impacted or troublesome wisdom teeth, with sedation available and clear aftercare.</p>
          <span class="more">Learn more</span>
        </a>
        <a class="service" href="#book">
          <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M6 22c4 8 11 13 18 13s14-5 18-13"/><path d="M10 21c3 6 8 9 14 9s11-3 14-9"/><path d="M14 25.5v4M19 28v4.5M24 29v5M29 28v4.5M34 25.5v4"/></svg>
          <h3>Clear Aligners</h3>
          <p>Discreet, removable aligners that straighten teeth gradually, planned from a 3D scan of your smile.</p>
          <span class="more">Learn more</span>
        </a>
        <a class="service" href="#book">
          <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="12" y="10" width="10" height="18" rx="5"/><rect x="26" y="10" width="10" height="18" rx="5"/><path d="M17 28v10M31 28v10M12 38h24"/></svg>
          <h3>Veneers &amp; Crowns</h3>
          <p>Hand-finished porcelain matched shade by shade to restore shape, strength and colour.</p>
          <span class="more">Learn more</span>
        </a>
        <a class="service" href="#book">
          <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M16 12c3-2 5-2 8 0 3-2 5-2 8 0 4 4 2 11 0 16-1 4-1 12-4 12-2 0-2-7-4-7s-2 7-4 7c-3 0-3-8-4-12-2-5-4-12 0-16Z"/><path d="M38 6v6M35 9h6M9 30v4M7 32h4"/></svg>
          <h3>Teeth Whitening</h3>
          <p>In-clinic and take-home whitening that lifts stains safely while protecting sensitive teeth.</p>
          <span class="more">Learn more</span>
        </a>
        <a class="service" href="#book">
          <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 6 8 18v4h32v-4L24 6Z"/><path d="M12 22v18h24V22"/><path d="M20 40V30h8v10"/></svg>
          <h3>General &amp; Preventive</h3>
          <p>Check-ups, hygiene, fillings and root canal treatment that keep your smile healthy for the long run.</p>
          <span class="more">Learn more</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Feature: implants -->
  <section class="section feature" id="implants">
    <div class="wrap">
      <div class="feature-copy">
        <p class="eyebrow eyebrow--light">Signature treatment</p>
        <h2>Dental implants, planned to the millimetre</h2>
        <p>Implants are the closest thing to a natural tooth. We map your bone and nerves in 3D, plan the position digitally and place each implant through a surgical guide for a faster, more comfortable recovery.</p>
        <ul class="checklist">
          <li>3D cone-beam imaging and digital treatment planning</li>
          <li>Single tooth, multiple teeth and full-arch solutions</li>
          <li>Bone grafting and sinus lift when extra support is needed</li>
          <li>Crowns shade-matched to your natural teeth</li>
        </ul>
        <div><a class="btn btn--light" href="#book">Plan Your Implant</a></div>
      </div>
      <div class="feature-media">
        <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/shades.jpg' ) ); ?>" alt="A porcelain shade guide used to match crowns to natural teeth" width="933" height="1400">
        <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/aligners.jpg' ) ); ?>" alt="Gloved hands holding a clear aligner" width="736" height="1104">
      </div>
    </div>
  </section>

  <!-- Journey -->
  <section class="section journey" id="visit">
    <div class="wrap">
      <div class="section-head">
        <p class="eyebrow">Your visit</p>
        <h2>What to expect at the Hauz</h2>
        <p>Whether you are coming in for a check-up or surgery, every patient follows the same clear path.</p>
      </div>
      <ol class="steps">
        <li class="step"><h3>Consultation</h3><p>Meet your dentist, talk through your goals and have a full examination with digital X-rays.</p></li>
        <li class="step"><h3>Scan &amp; plan</h3><p>We take a 3D scan and give you a written plan with timings, options and costs.</p></li>
        <li class="step"><h3>Treatment</h3><p>Your care is carried out in a calm private suite, with comfort and sedation options explained.</p></li>
        <li class="step"><h3>Aftercare</h3><p>Follow-up visits and a direct line to the team while you heal and settle in.</p></li>
      </ol>
    </div>
  </section>

  <!-- Manifesto -->
  <section class="section manifesto">
    <div class="wrap">
      <div class="manifesto-media">
        <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/oral-health.jpg' ) ); ?>" alt="A clinician holding a dental model. Text reads: Oral health is more than just health." width="736" height="981">
      </div>
      <div class="manifesto-copy">
        <div class="mantra">
          <span>Your smile</span>
          <span>Your experience</span>
          <span>Your <b>Hauz</b></span>
        </div>
        <blockquote>Oral health is more than just health. It is how you eat, speak, smile and feel.</blockquote>
        <p>That is why we treat the whole person, not only the tooth. We take time to listen, explain the options in plain language and design care that fits your life.</p>
        <div><a class="btn btn--outline-light" href="#book">Meet the Team</a></div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section faq" id="faq">
    <div class="wrap">
      <div class="section-head">
        <p class="eyebrow">Questions</p>
        <h2>Good to know before you visit</h2>
        <p>Can't find your answer? Send us a message and the team will get back to you.</p>
      </div>
      <div class="faq-list">
        <details open>
          <summary>Do implants hurt?</summary>
          <p>Implant surgery is done under local anaesthesia, and sedation is available if you feel anxious. Most patients describe the recovery as easier than a tooth extraction, and we give you a clear aftercare plan.</p>
        </details>
        <details>
          <summary>How long does implant treatment take?</summary>
          <p>The implant usually needs a few months to bond with the bone before the final crown is fitted. In suitable cases we can place a temporary tooth on the same day. Your plan will show your exact timeline.</p>
        </details>
        <details>
          <summary>Should my wisdom teeth come out?</summary>
          <p>Not always. We recommend removal when wisdom teeth are impacted, causing pain or infection, or putting neighbouring teeth at risk. A consultation and X-ray will tell us which applies to you.</p>
        </details>
        <details>
          <summary>Are clear aligners right for me?</summary>
          <p>Aligners suit most adults and teens with crowding, gaps or bite issues. After a 3D scan we can show you a preview of how your teeth will move before you commit.</p>
        </details>
        <details>
          <summary>Do you offer payment plans?</summary>
          <p>Yes. Every plan includes a written cost breakdown, and we can spread the cost of larger treatments into monthly instalments.</p>
        </details>
      </div>
    </div>
  </section>

  <!-- Book -->
  <section class="section book" id="book">
    <div class="wrap">
      <div class="book-copy">
        <p class="eyebrow">Book a consultation</p>
        <h2>Start your smile at the Hauz</h2>
        <p>Tell us a little about what you need and our team will call you to arrange a time that suits you.</p>
        <?php
        $dh_phone   = (string) get_theme_mod( 'dh_phone', '' );
        $dh_email   = (string) get_theme_mod( 'dh_email', '' );
        $dh_address = (string) get_theme_mod( 'dh_address', '' );
        ?>
        <?php if ( $dh_phone || $dh_email || $dh_address ) : ?>
        <dl class="contact-lines">
          <?php if ( $dh_phone ) : ?><div><dt><?php esc_html_e( 'Phone', 'the-dental-hauz' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dh_phone ) ); ?>"><?php echo esc_html( $dh_phone ); ?></a></dd></div><?php endif; ?>
          <?php if ( $dh_email ) : ?><div><dt><?php esc_html_e( 'Email', 'the-dental-hauz' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( antispambot( $dh_email ) ); ?>"><?php echo esc_html( antispambot( $dh_email ) ); ?></a></dd></div><?php endif; ?>
          <?php if ( $dh_address ) : ?><div><dt><?php esc_html_e( 'Visit', 'the-dental-hauz' ); ?></dt><dd><?php echo nl2br( esc_html( $dh_address ) ); ?></dd></div><?php endif; ?>
        </dl>
        <?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
        <p><em><?php esc_html_e( 'Admins: add the phone, email and address under Appearance → Customize → Clinic details.', 'the-dental-hauz' ); ?></em></p>
        <?php endif; ?>
      </div>
      <form class="form" id="booking-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="dh_booking">
        <div class="hp-field" aria-hidden="true"><label for="f-website">Website</label><input id="f-website" type="text" name="dh_website" tabindex="-1" autocomplete="off"></div>
        <?php $dh_notice = dh_booking_notice(); ?>
        <?php if ( $dh_notice ) : ?>
          <p class="form-note <?php echo 'is-error' === $dh_notice[0] ? 'is-error' : ''; ?>" id="form-note" role="status"><?php echo esc_html( $dh_notice[1] ); ?></p>
        <?php endif; ?>
        <div class="row">
          <div class="field"><label for="f-name"><?php esc_html_e( 'Full name', 'the-dental-hauz' ); ?></label><input id="f-name" type="text" name="dh_name" autocomplete="name" required maxlength="100"></div>
          <div class="field"><label for="f-phone"><?php esc_html_e( 'Phone', 'the-dental-hauz' ); ?></label><input id="f-phone" name="dh_phone" type="tel" autocomplete="tel" required maxlength="40"></div>
        </div>
        <div class="field"><label for="f-email"><?php esc_html_e( 'Email', 'the-dental-hauz' ); ?></label><input id="f-email" type="email" name="dh_email" autocomplete="email" maxlength="100"></div>
        <div class="field">
          <label for="f-treatment"><?php esc_html_e( 'Treatment', 'the-dental-hauz' ); ?></label>
          <select id="f-treatment" name="dh_treatment">
            <?php foreach ( dh_treatments() as $dh_t ) : ?>
              <option value="<?php echo esc_attr( $dh_t ); ?>"><?php echo esc_html( $dh_t ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="f-message"><?php esc_html_e( 'Message', 'the-dental-hauz' ); ?></label><textarea id="f-message" name="dh_message" rows="3" maxlength="2000"></textarea></div>
        <button class="btn" type="submit"><?php esc_html_e( 'Request Appointment', 'the-dental-hauz' ); ?></button>
      </form>
    </div>
  </section>
<?php get_footer(); ?>
