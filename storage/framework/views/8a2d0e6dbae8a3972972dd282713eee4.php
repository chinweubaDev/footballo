<?php $__env->startSection('title', 'Blog - Football & Sports News, Predictions & Insights'); ?>
<?php $__env->startSection('meta_description', 'Read the latest soccer, basketball, hockey and tennis news, match previews, betting tips and expert analysis from EsureBet.'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-slate-950 min-h-screen pb-20">
    
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-28 pb-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-primary-500/10 blur-[120px] rounded-full"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-[10px] font-black uppercase tracking-[0.2em] mb-6" data-aos="fade-down">
                <i class="fas fa-newspaper mr-2"></i> EsureBet Blog
            </div>
            <h1 class="text-5xl lg:text-6xl font-black text-white mb-4 tracking-tight" data-aos="fade-up">
                Sports News & <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Predictions</span>
            </h1>
            <p class="text-slate-400 text-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Expert analysis, match previews, betting tips and the latest news across soccer, basketball, hockey and tennis.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid lg:grid-cols-4 gap-8">
            
            <div class="lg:col-span-3">
                
                <div class="flex flex-wrap gap-2 mb-8" data-aos="fade-up">
                    <a href="<?php echo e(route('blog.index')); ?>" class="px-5 py-2.5 rounded-2xl text-sm font-bold transition-all <?php echo e(!request('category') ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'); ?>">
                        All (<?php echo e(array_sum($categories)); ?>)
                    </a>
                    <?php $__currentLoopData = ['soccer' => '⚽', 'basketball' => '🏀', 'hockey' => '🏒', 'tennis' => '🎾']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat => $emoji): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('blog.index', ['category' => $cat])); ?>" class="px-5 py-2.5 rounded-2xl text-sm font-bold transition-all <?php echo e(request('category') === $cat ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'); ?>">
                            <?php echo e($emoji); ?> <?php echo e(ucfirst($cat)); ?> (<?php echo e($categories[$cat]); ?>)
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <form method="GET" action="<?php echo e(route('blog.index')); ?>" class="mb-8" data-aos="fade-up">
                    <div class="relative">
                        <input type="text" name="s" value="<?php echo e(request('s')); ?>" placeholder="Search articles..." class="w-full bg-slate-900/50 border border-white/10 text-white text-sm rounded-2xl pl-12 pr-6 py-4 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none backdrop-blur-xl">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <?php if(request('category')): ?>
                            <input type="hidden" name="category" value="<?php echo e(request('category')); ?>">
                        <?php endif; ?>
                    </div>
                </form>

                
                <?php if($posts->count() > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="group bg-slate-900/50 backdrop-blur-xl rounded-[2rem] border border-white/5 overflow-hidden hover:border-primary-500/30 transition-all duration-500 hover:shadow-xl hover:shadow-primary-500/5" data-aos="fade-up">
                            <a href="<?php echo e(route('blog.show', $post->slug)); ?>" class="block">
                                
                                <div class="relative h-48 md:h-52 overflow-hidden bg-slate-800">
                                    <?php if($post->featured_image): ?>
                                        <img src="<?php echo e($post->featured_image); ?>" alt="<?php echo e($post->title); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <?php $emoji = ['soccer' => '⚽', 'basketball' => '🏀', 'hockey' => '🏒', 'tennis' => '🎾'][$post->category] ?? '📰'; ?>
                                            <span class="text-6xl opacity-20"><?php echo e($emoji); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                                    <div class="absolute top-4 left-4 flex gap-2">
                                        <span class="px-3 py-1 bg-primary-600/90 text-white text-[10px] font-black uppercase tracking-widest rounded-full backdrop-blur-sm"><?php echo e($post->category_label); ?></span>
                                        <?php if($post->tags && count($post->tagList) > 0): ?>
                                            <span class="px-3 py-1 bg-slate-900/80 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-full backdrop-blur-sm">#<?php echo e($post->tagList[0]); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                
                                <div class="p-6">
                                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">
                                        <span><?php echo e($post->published_at->format('M d, Y')); ?></span>
                                        <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                        <span><?php echo e($post->author); ?></span>
                                    </div>

                                    <h2 class="text-lg font-black text-white mb-3 group-hover:text-primary-400 transition-colors leading-tight line-clamp-2">
                                        <?php echo e($post->title); ?>

                                    </h2>

                                    <p class="text-sm text-slate-400 leading-relaxed line-clamp-3">
                                        <?php echo e($post->excerpt); ?>

                                    </p>

                                    
                                    <?php if($post->tags && count($post->tagList) > 0): ?>
                                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-white/5">
                                        <?php $__currentLoopData = array_slice($post->tagList, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(route('blog.index', ['tag' => $tag])); ?>" class="text-[10px] font-bold text-slate-500 hover:text-primary-400 transition-colors">#<?php echo e($tag); ?></a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="mt-12">
                        <?php echo e($posts->withQueryString()->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-[3rem] p-16 text-center border border-white/5">
                        <div class="w-20 h-20 bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-newspaper text-slate-600 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-3">No Articles Yet</h3>
                        <p class="text-slate-400">Our blog posts are being prepared. Check back soon for the latest sports news and predictions.</p>
                    </div>
                <?php endif; ?>
            </div>

            
            <aside class="space-y-6">
                
                <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2rem] border border-white/5 overflow-hidden" data-aos="fade-up">
                    <div class="px-6 py-4 border-b border-white/5">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Categories</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <?php $__currentLoopData = ['soccer' => '⚽ Soccer', 'basketball' => '🏀 Basketball', 'hockey' => '🏒 Hockey', 'tennis' => '🎾 Tennis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('blog.index', ['category' => $cat])); ?>" class="flex items-center justify-between group">
                                <span class="text-sm font-bold text-slate-400 group-hover:text-white transition-colors"><?php echo e($label); ?></span>
                                <span class="text-xs font-bold text-slate-600 bg-slate-800 px-2 py-1 rounded-full"><?php echo e($categories[$cat]); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <?php if($popularTags->count() > 0): ?>
                <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2rem] border border-white/5 overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                    <div class="px-6 py-4 border-b border-white/5">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Popular Tags</h3>
                    </div>
                    <div class="p-6 flex flex-wrap gap-2">
                        <?php $__currentLoopData = $popularTags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('blog.index', ['tag' => $tag])); ?>" class="px-3 py-1.5 bg-slate-800 text-slate-400 text-xs font-bold rounded-xl hover:bg-primary-600 hover:text-white transition-all">
                                #<?php echo e($tag); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>

                
                <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-[2rem] p-6 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-crown text-white text-xl"></i>
                    </div>
                    <h4 class="text-white font-black text-lg mb-2">Get Premium Access</h4>
                    <p class="text-primary-200 text-sm mb-6">Unlock expert predictions with 99% accuracy.</p>
                    <a href="<?php echo e(route('pricing')); ?>" class="inline-flex items-center px-6 py-3 bg-white text-primary-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-primary-50 transition-all">
                        Upgrade Now <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/blog/index.blade.php ENDPATH**/ ?>