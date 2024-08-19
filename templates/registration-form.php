<!-- Include the HTML and PHP logic for the affiliate registration form here -->
<div class="affiliate-register-form">
        <h2>Register</h2>
        <form method="post">
            <p>
                <label for="first_name">First name:</label>
                <input type="text" name="first_name" value="<?php echo esc_attr($_POST['first_name'] ?? ''); ?>" style="<?php echo isset($errors['first_name']) ? 'border-color:red;' : ''; ?>" required>
                <?php if (isset($errors['first_name'])): ?>
                    <span style="color:red;"><?php echo $errors['first_name']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="last_name">Last name:</label>
                <input type="text" name="last_name" value="<?php echo esc_attr($_POST['last_name'] ?? ''); ?>" style="<?php echo isset($errors['last_name']) ? 'border-color:red;' : ''; ?>" required>
                <?php if (isset($errors['last_name'])): ?>
                    <span style="color:red;"><?php echo $errors['last_name']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" style="<?php echo isset($errors['email']) ? 'border-color:red;' : ''; ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span style="color:red;"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="phone">Phone number:</label>
                <select name="country_code" style="width: 100px;" required>
                <option value="+60">Malaysia (+60)</option>
                    <option value="+65">Singapore (+65)</option>
                    <option value="+66">Thailand (+66)</option>
                    <option value="+61">Australia (+61)</option>
                    <option value="+1">United States (+1)</option>
                    <option value="+44">United Kingdom (+44)</option>
                    <option value="+81">Japan (+81)</option>
                    <option value="+86">China (+86)</option>
                    <!-- 添加更多国家代码 -->
                </select>
                <input type="text" name="phone" value="<?php echo esc_attr($_POST['phone'] ?? ''); ?>" placeholder="1234567890" style="<?php echo isset($errors['phone']) ? 'border-color:red;' : ''; ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <span style="color:red;"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>

            </p>
            <p>
                <label for="password">Password (min. 8 characters):</label>
                <input type="password" name="password" style="<?php echo isset($errors['password']) ? 'border-color:red;' : ''; ?>" required minlength="8">
                <?php if (isset($errors['password'])): ?>
                    <span style="color:red;"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" style="<?php echo isset($errors['confirm_password']) ? 'border-color:red;' : ''; ?>" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span style="color:red;"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="address">Your Address:</label>
                <input type="text" name="address" value="<?php echo esc_attr($_POST['address'] ?? ''); ?>" placeholder="Street Address" style="<?php echo isset($errors['address']) ? 'border-color:red;' : ''; ?>" required>
            </p>
            <p>
                <input type="text" name="postcode" value="<?php echo esc_attr($_POST['postcode'] ?? ''); ?>" placeholder="Post Code" required style="<?php echo isset($errors['address']) ? 'border-color:red;' : ''; ?>">
                <input type="text" name="city" value="<?php echo esc_attr($_POST['city'] ?? ''); ?>" placeholder="City" required style="<?php echo isset($errors['address']) ? 'border-color:red;' : ''; ?>">
                <input type="text" name="state" value="<?php echo esc_attr($_POST['state'] ?? ''); ?>" placeholder="State" required style="<?php echo isset($errors['address']) ? 'border-color:red;' : ''; ?>">
            </p>
            <p>
                <label for="coupon_code">Preferred Coupon Code:</label>
                <input type="text" name="coupon_code" value="<?php echo esc_attr($_POST['coupon_code'] ?? ''); ?>" required style="<?php echo isset($errors['coupon_code']) ? 'border-color:red;' : ''; ?>">
                <?php if (isset($errors['coupon_code'])): ?>
                    <span style="color:red;"><?php echo $errors['coupon_code']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="followers">How many followers do you have?</label>
                <input type="number" name="followers" value="<?php echo esc_attr($_POST['followers'] ?? ''); ?>" required style="<?php echo isset($errors['followers']) ? 'border-color:red;' : ''; ?>">
                <?php if (isset($errors['followers'])): ?>
                    <span style="color:red;"><?php echo $errors['followers']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="social_media">Which social media do you use?</label><br>
                <input type="checkbox" name="social_media[]" value="Xiaohongshu" <?php echo in_array('Xiaohongshu', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> 小红书<br>
                <input type="checkbox" name="social_media[]" value="Instagram" <?php echo in_array('Instagram', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> Instagram<br>
                <input type="checkbox" name="social_media[]" value="Facebook" <?php echo in_array('Facebook', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> Facebook<br>
                <input type="checkbox" name="social_media[]" value="Tiktok" <?php echo in_array('Tiktok', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> Tiktok<br>
                <input type="checkbox" name="social_media[]" value="Douyin" <?php echo in_array('Douyin', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> 抖音<br>
                <input type="checkbox" name="social_media[]" value="Other" <?php echo in_array('Other', $_POST['social_media'] ?? []) ? 'checked' : ''; ?>> Other: <input type="text" name="social_media_other" value="<?php echo esc_attr($_POST['social_media_other'] ?? ''); ?>">
            </p>
            <p>
                <label for="social_id">What is your social media ID?</label>
                <input type="text" name="social_id" value="<?php echo esc_attr($_POST['social_id'] ?? ''); ?>" required style="<?php echo isset($errors['social_id']) ? 'border-color:red;' : ''; ?>">
            </p>
            <p>
                <label for="promotion">How will you promote us?</label><br>
                <textarea name="promotion" required style="<?php echo isset($errors['promotion']) ? 'border-color:red;' : ''; ?>"><?php echo esc_textarea($_POST['promotion'] ?? ''); ?></textarea>
            </p>
            <p>
                <label for="referral">How did you hear about us?</label><br>
                <textarea name="referral" required style="<?php echo isset($errors['referral']) ? 'border-color:red;' : ''; ?>"><?php echo esc_textarea($_POST['referral'] ?? ''); ?></textarea>
            </p>
            <p>
                <input type="checkbox" name="agree_terms" required <?php checked(isset($_POST['agree_terms'])); ?>> I agree to the <a href="<?php echo site_url('/terms'); ?>" target="_blank">affiliate terms</a> and <a href="<?php echo site_url('/privacy'); ?>" target="_blank">privacy policy</a>.
                <?php if (isset($errors['agree_terms'])): ?>
                    <span style="color:red;"><?php echo $errors['agree_terms']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <input type="submit" name="affiliate_register" value="Submit Application">
            </p>
            <?php wp_nonce_field('affiliate_register_action', 'affiliate_register_nonce'); ?>
        </form>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="error-message" style="color:red;">Please fix the errors below and try again.</div>
<?php endif; ?>
