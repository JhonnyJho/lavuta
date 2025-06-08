<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    posts: Array,
    userId: Number
});

const selectedPost = ref(null);


function rate(post, value) {
    router.post(route('posts.rate', post.id), { rating: value }, {
        preserveScroll: true,
        onSuccess: (page) => {
            // Update the post's rating info instantly
            const updated = page.props.posts?.find(p => p.id === post.id);
            if (updated) {
                post.avg_rating = updated.avg_rating;
                post.user_rating = updated.user_rating;
                post.ratings_count = updated.ratings_count;
            }
        }
    });
}

function unrate(post) {
    router.delete(route('posts.unrate', post.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Update the post's rating info instantly
            const updated = page.props.posts?.find(p => p.id === post.id);
            if (updated) {
                post.avg_rating = updated.avg_rating;
                post.user_rating = updated.user_rating;
                post.ratings_count = updated.ratings_count;
            }
        }
    });
}
</script>

<template>
    <div class="max-w-6xl mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-8 text-center">All Posts</h1>
        <div v-if="posts.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <div
                v-for="post in posts"
                :key="post.id"
                class="bg-white rounded shadow p-4 cursor-pointer hover:shadow-lg transition"
                @click="selectedPost = post"
            >
                <h3 class="font-bold text-lg mb-2">{{ post.title }}</h3>
                <img
                    v-if="post.image"
                    :src="'/storage/' + post.image"
                    class="w-full rounded mb-2"
                    alt="Post Image"
                />
                <div class="flex items-center mt-2">
                    <Link
                        :href="route('users.profile', post.user.id)"
                        class="flex items-center space-x-2 group"
                        @click.stop
                    >
                        <img
                            :src="post.user.avatar ? ('/storage/' + post.user.avatar) : '/storage/avatars/defaultpfp.png'"
                            class="w-6 h-6 rounded-full"
                            alt="Author Avatar"
                        />
                        <span class="text-sm text-blue-600 group-hover:underline">{{ post.user.name }}</span>
                    </Link>
                </div>

                <!-- Show stars and rating info -->
                <div class="flex items-center mb-2">
                    <template v-for="star in 5">
                        <button
                            v-if="props.userId && post.user_id !== props.userId"
                            :key="star"
                            class="text-yellow-400 focus:outline-none"
                            :class="{'opacity-50': (post.user_rating ?? 0) < star}"
                            @click.stop="rate(post, star)"
                            :disabled="(post.user_id === props.userId) || (post.user_rating === star)"
                            title="Rate"
                        >★</button>
                        <span v-else :key="'star-else-' + star" class="text-yellow-200">★</span>
                    </template>
                    <span class="ml-2 text-sm text-gray-600">
                        {{ post.avg_rating ? post.avg_rating.toFixed(2) : '0.00' }} / 5
                        ({{ post.ratings_count ?? 0 }} votes)
                    </span>
                    <button
                        v-if="userId && post.user_rating && post.user_id !== userId"
                        class="ml-2 text-xs text-red-500 underline"
                        @click.stop="unrate(post)"
                    >Cancel</button>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-gray-400">No posts yet.</div>

        <!-- Post Modal -->
        <div v-if="selectedPost" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow-lg max-w-lg w-full p-6 relative">
                <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="selectedPost = null">&times;</button>
                <h3 class="font-bold text-2xl mb-2">{{ selectedPost.title }}</h3>
                <img
                    v-if="selectedPost.image"
                    :src="'/storage/' + selectedPost.image"
                    class="w-full rounded mb-4"
                    alt="Post Image"
                />
                <p class="mb-4">{{ selectedPost.body }}</p>
                <div class="flex items-center">
                    <Link
                        :href="route('users.profile', selectedPost.user.id)"
                        class="flex items-center space-x-2 group"
                        @click.stop
                    >
                        <img
                            :src="selectedPost.user.avatar ? ('/storage/' + selectedPost.user.avatar) : '/storage/avatars/defaultpfp.png'"
                            class="w-6 h-6 rounded-full"
                            alt="Author Avatar"
                        />
                        <span class="text-sm text-blue-600 group-hover:underline">{{ selectedPost.user.name }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>