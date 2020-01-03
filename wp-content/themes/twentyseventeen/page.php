<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

 $cpds = get_field('cpd_records');

get_header(); ?>




<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php

			while ( have_posts() ) :
				the_post();
			the_field(date);
			 get_template_part( 'template-parts/page/content', 'page' );

			 if ($cpds) : ?>
			 <section class="cpd-section">
				 <ul>
				 <?php
				     foreach($cpds as $cpd) : ?>
				    	<?php
						$cpd_title = $cpd['title'];
						$cpd_start = $cpd['start_date'];
						$cpd_end = $cpd['end_date'];
						$start_time = $cpd['start_time'];
						$end_time = $cpd['end_time'];
						if (empty($cpd_end) || !$cpd_end) {
							$cpd_end = $cpd_start;
						}
						$cpd_description = $cpd['description'];
						$cpd_link = $cpd['link'];
						$new_time = $cpd_end - $cpd_start;
						?>
						<li class="cpd-record">
							<div class="title">
							<?php if ($cpd_link) : ?>
								<a href="<?=$cpd_link?>">
									<h3 class="cpd-title"><?=$cpd_title?> - </h3>
									<span><?=$cpd_start?></span>
								</a>
							<?php else : ?>
								<h3 class="cpd-title"><?=$cpd_title?> - </h3>
								<span><?=$cpd_start?></span>
							<?php endif; ?>
							</div>
							<p><?=$cpd_description?></p>
							<?php if (strtotime($cpd_end) > strtotime($cpd_start)) : ?>
								<?php $diff = strtotime($cpd_end) - strtotime($cpd_start);
								$days = $diff / (60*60*24);
								if ($days === 1) : ?>
									<p>length: <?=$days?> day</p>
								<?php else : ?>
									<p>length: <?=$days?> days</p>
								<?php endif; ?>
							<?php else : ?>
								<?php if ($end_time) : ?>
									<?php $diff = strtotime($end_time) - strtotime($start_time);
									$hours = $diff / 3600;
									if ($hours === 1) : ?>
										<p>length: <?=$hours?> hour</p>
									<?php else : ?>
										<p>length: <?=$hours?> hours</p>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ($cpd_link) : ?>
								<a href="<?=$cpd_link?>" class="btn">See more</a>
							<?php endif; ?>
						</li>
				     <?php endforeach; ?>
				 </ul>
			 </section>
			 <?php endif;

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.

			?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
get_footer();
