<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $path => $priority): ?>
	<url>
		<loc><?php echo html_escape(base_url($path)); ?></loc>
		<priority><?php echo $priority; ?></priority>
	</url>
<?php endforeach; ?>
<?php foreach ($products as $product): ?>
	<url>
		<loc><?php echo html_escape(base_url('producto/' . (int) $product->id)); ?></loc>
<?php if ( ! empty($product->updated_at)): ?>
		<lastmod><?php echo html_escape(date('Y-m-d', strtotime($product->updated_at))); ?></lastmod>
<?php endif; ?>
		<priority>0.8</priority>
	</url>
<?php endforeach; ?>
</urlset>
