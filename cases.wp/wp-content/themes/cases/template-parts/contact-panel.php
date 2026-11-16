<?php
/**
 * Contact slide-over panel (opened by any [data-open-contact] element).
 *
 * @package cases
 */

defined( 'ABSPATH' ) || exit;

$phone = cases_opt( 'phone' );
$email = cases_opt( 'email' );
?>
<div id="contact-overlay" class="contact-overlay" hidden aria-hidden="true"></div>

<aside id="contact-panel" class="contact-panel" role="dialog" aria-modal="true" aria-labelledby="contact-title" aria-hidden="true" tabindex="-1">
	<div class="contact-panel__head">
		<div>
			<p class="contact-panel__eyebrow"<?php cases_attr( 'Get In Touch', 'Liên Hệ Với Chúng Tôi' ); ?>>Get In Touch</p>
			<h2 id="contact-title" class="contact-panel__title"<?php cases_attr( 'Contact CASES', 'Liên Hệ CASES' ); ?>>Contact CASES</h2>
		</div>
		<button type="button" class="contact-panel__close" data-close-contact aria-label="<?php esc_attr_e( 'Close', 'cases' ); ?>">&#10005;</button>
	</div>

	<div class="contact-panel__body">
		<div class="flex flex-col gap-3">
			<a class="contact-card" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>">
				<span class="contact-card__icon"><span class="material-symbols-outlined" aria-hidden="true">call</span></span>
				<span>
					<span class="contact-card__label"<?php cases_attr( 'Phone', 'Điện Thoại' ); ?>>Phone</span>
					<span class="contact-card__value"><?php echo esc_html( $phone ); ?></span>
				</span>
			</a>
			<a class="contact-card" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>">
				<span class="contact-card__icon"><span class="material-symbols-outlined" aria-hidden="true">mail</span></span>
				<span>
					<span class="contact-card__label">Email</span>
					<span class="contact-card__value"><?php echo esc_html( antispambot( $email ) ); ?></span>
				</span>
			</a>
			<div class="contact-card">
				<span class="contact-card__icon"><span class="material-symbols-outlined" aria-hidden="true">location_on</span></span>
				<span>
					<span class="contact-card__label"<?php cases_attr( 'Address', 'Địa Chỉ' ); ?>>Address</span>
					<span class="contact-card__value contact-card__value--normal"><?php echo esc_html( cases_opt( 'address' ) ); ?></span>
				</span>
			</div>
		</div>

		<div class="contact-divider"><span<?php cases_attr( 'Send a Message', 'Gửi Tin Nhắn' ); ?>>Send a Message</span></div>

		<form id="contact-form" class="flex flex-col gap-4" novalidate>
			<div class="grid grid-cols-2 gap-3">
				<div>
					<label class="contact-label" for="cf-name"<?php cases_attr( 'Full Name', 'Họ Tên' ); ?>>Full Name</label>
					<input class="contact-input" type="text" id="cf-name" name="name" placeholder="John Smith" autocomplete="name" required>
				</div>
				<div>
					<label class="contact-label" for="cf-company"<?php cases_attr( 'Company', 'Công Ty' ); ?>>Company</label>
					<input class="contact-input" type="text" id="cf-company" name="company" placeholder="Your Company" autocomplete="organization">
				</div>
			</div>
			<div>
				<label class="contact-label" for="cf-email"<?php cases_attr( 'Email Address', 'Địa Chỉ Email' ); ?>>Email Address</label>
				<input class="contact-input" type="email" id="cf-email" name="email" placeholder="you@company.com" autocomplete="email" required>
			</div>
			<div>
				<label class="contact-label" for="cf-message"<?php cases_attr( 'Message', 'Nội Dung' ); ?>>Message</label>
				<textarea class="contact-input resize-y leading-normal" id="cf-message" name="message" rows="4" placeholder="Tell us about your inquiry — product interests, volume requirements, market destination..." required></textarea>
			</div>
			<!-- Honeypot -->
			<div class="absolute -left-[9999px]" aria-hidden="true">
				<label for="cf-website">Website</label>
				<input type="text" id="cf-website" name="company_website" tabindex="-1" autocomplete="off">
			</div>
			<p id="contact-error" class="contact-error" role="alert" hidden></p>
			<button type="submit" class="contact-submit" id="contact-submit">
				<span class="material-symbols-outlined text-base" aria-hidden="true">send</span>
				<span<?php cases_attr( 'Send Message', 'Gửi Tin Nhắn' ); ?>>Send Message</span>
			</button>
			<div id="contact-success" class="contact-success" role="status" hidden>
				<p><?php cases_t( '✓ Message sent! We\'ll be in touch soon.', '✓ Đã gửi tin nhắn! Chúng tôi sẽ sớm liên hệ lại.' ); ?></p>
			</div>
		</form>
	</div>

	<div class="contact-panel__foot">
		<p>CaMau Seafood Processing &amp; Service JSC &middot; Est. 1996</p>
	</div>
</aside>
