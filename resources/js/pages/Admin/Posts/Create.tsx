import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { cn } from '@/lib/utils';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Image, Save, Tag as TagIcon } from 'lucide-react';
import React, { useState } from 'react';

interface Category {
    id: number;
    name: string;
}

interface Tag {
    id: number;
    name: string;
    color: string;
}

interface Media {
    id: number;
    name: string;
    path: string;
    alt_text: string | null;
}

interface CreateProps {
    categories: Category[];
    tags: Tag[];
    media: Media[];
}

export default function Create({ categories, tags, media }: CreateProps) {
    const [selectedTags, setSelectedTags] = useState<number[]>([]);
    const [showMediaLibrary, setShowMediaLibrary] = useState(false);
    const [selectedMedia, setSelectedMedia] = useState<Media | null>(null);
    const [mediaSearch, setMediaSearch] = useState('');

    const { data, setData, post, processing, errors, transform } = useForm({
        title: '',
        slug: '',
        excerpt: '',
        content: '',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        status: 'draft',
        published_at: '',
        scheduled_at: '',
        is_featured: false,
        allow_comments: true,
        is_sticky: false,
        category_id: 'none',
        featured_image_id: 'none',
        tag_ids: [] as number[],
        sort_order: 0,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Posts', href: '/admin/posts' },
        { title: 'Create', href: '/admin/posts/create' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Convert "none" values back to empty string for backend
        const formData = {
            ...data,
            category_id: data.category_id === 'none' ? '' : data.category_id,
            featured_image_id: data.featured_image_id === 'none' ? '' : data.featured_image_id,
        };
        
        // Update the form data and submit
        transform(() => formData);
        post(route('admin.posts.store'));
    };

    const generateSlug = (title: string) => {
        return title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    };

    const handleTitleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const title = e.target.value;
        setData('title', title);

        // Auto-generate slug if it's empty or matches the current title's slug
        if (!data.slug || data.slug === generateSlug(data.title)) {
            setData('slug', generateSlug(title));
        }

        // Auto-generate meta title if empty
        if (!data.meta_title) {
            setData('meta_title', title);
        }
    };

    const handleTagToggle = (tagId: number) => {
        const newSelectedTags = selectedTags.includes(tagId) ? selectedTags.filter((id) => id !== tagId) : [...selectedTags, tagId];

        setSelectedTags(newSelectedTags);
        setData('tag_ids', newSelectedTags);
    };

    const handleMediaSelect = (mediaItem: Media) => {
        setSelectedMedia(mediaItem);
        setData('featured_image_id', mediaItem.id.toString());
        setShowMediaLibrary(false);
    };

    const filteredMedia = media.filter((item) => item.name.toLowerCase().includes(mediaSearch.toLowerCase()));

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Post" />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.posts.index')}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Posts
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Post</h1>
                        <p className="text-muted-foreground">Write and publish a new blog post</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Main Content */}
                        <div className="space-y-6 lg:col-span-2">
                            {/* Basic Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Post Content</CardTitle>
                                    <CardDescription>The main content of your blog post</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="title">Title *</Label>
                                        <Input
                                            id="title"
                                            type="text"
                                            value={data.title}
                                            onChange={handleTitleChange}
                                            className={cn(errors.title && 'border-destructive')}
                                            placeholder="Enter post title"
                                        />
                                        {errors.title && <p className="text-sm text-destructive">{errors.title}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="slug">Slug</Label>
                                        <Input
                                            id="slug"
                                            type="text"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            className={cn(errors.slug && 'border-destructive')}
                                            placeholder="post-url-slug"
                                        />
                                        {errors.slug && <p className="text-sm text-destructive">{errors.slug}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="excerpt">Excerpt</Label>
                                        <Textarea
                                            id="excerpt"
                                            value={data.excerpt}
                                            onChange={(e) => setData('excerpt', e.target.value)}
                                            className={cn(errors.excerpt && 'border-destructive')}
                                            placeholder="Brief summary of the post"
                                            rows={3}
                                        />
                                        {errors.excerpt && <p className="text-sm text-destructive">{errors.excerpt}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="content">Content *</Label>
                                        <Textarea
                                            id="content"
                                            value={data.content}
                                            onChange={(e) => setData('content', e.target.value)}
                                            className={cn(errors.content && 'border-destructive', 'min-h-[300px]')}
                                            placeholder="Write your post content here..."
                                        />
                                        {errors.content && <p className="text-sm text-destructive">{errors.content}</p>}
                                        <p className="text-sm text-muted-foreground">You can use HTML or Markdown formatting</p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* SEO Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Settings</CardTitle>
                                    <CardDescription>Optimize your post for search engines</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="meta_title">Meta Title</Label>
                                        <Input
                                            id="meta_title"
                                            type="text"
                                            value={data.meta_title}
                                            onChange={(e) => setData('meta_title', e.target.value)}
                                            placeholder="SEO optimized title"
                                        />
                                        <p className="text-sm text-muted-foreground">{data.meta_title.length}/60 characters</p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="meta_description">Meta Description</Label>
                                        <Textarea
                                            id="meta_description"
                                            value={data.meta_description}
                                            onChange={(e) => setData('meta_description', e.target.value)}
                                            placeholder="Brief description for search results"
                                            rows={3}
                                        />
                                        <p className="text-sm text-muted-foreground">{data.meta_description.length}/160 characters</p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="meta_keywords">Meta Keywords</Label>
                                        <Input
                                            id="meta_keywords"
                                            type="text"
                                            value={data.meta_keywords}
                                            onChange={(e) => setData('meta_keywords', e.target.value)}
                                            placeholder="keyword1, keyword2, keyword3"
                                        />
                                        <p className="text-sm text-muted-foreground">Separate keywords with commas</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Publish Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Publish Settings</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="published">Published</SelectItem>
                                                <SelectItem value="scheduled">Scheduled</SelectItem>
                                                <SelectItem value="archived">Archived</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    {data.status === 'published' && (
                                        <div className="space-y-2">
                                            <Label htmlFor="published_at">Publish Date</Label>
                                            <Input
                                                id="published_at"
                                                type="datetime-local"
                                                value={data.published_at}
                                                onChange={(e) => setData('published_at', e.target.value)}
                                            />
                                        </div>
                                    )}

                                    {data.status === 'scheduled' && (
                                        <div className="space-y-2">
                                            <Label htmlFor="scheduled_at">Schedule Date</Label>
                                            <Input
                                                id="scheduled_at"
                                                type="datetime-local"
                                                value={data.scheduled_at}
                                                onChange={(e) => setData('scheduled_at', e.target.value)}
                                            />
                                        </div>
                                    )}

                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="is_featured">Featured Post</Label>
                                        <Switch
                                            id="is_featured"
                                            checked={data.is_featured}
                                            onCheckedChange={(checked) => setData('is_featured', checked)}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="allow_comments">Allow Comments</Label>
                                        <Switch
                                            id="allow_comments"
                                            checked={data.allow_comments}
                                            onCheckedChange={(checked) => setData('allow_comments', checked)}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="is_sticky">Sticky Post</Label>
                                        <Switch
                                            id="is_sticky"
                                            checked={data.is_sticky}
                                            onCheckedChange={(checked) => setData('is_sticky', checked)}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                            min="0"
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Category */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Category</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Select value={data.category_id} onValueChange={(value) => setData('category_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">No Category</SelectItem>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </CardContent>
                            </Card>

                            {/* Tags */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tags</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex flex-wrap gap-2">
                                            {selectedTags.map((tagId) => {
                                                const tag = tags.find((t) => t.id === tagId);
                                                return tag ? (
                                                    <Badge
                                                        key={tag.id}
                                                        variant="secondary"
                                                        className="cursor-pointer"
                                                        style={{ backgroundColor: `${tag.color}20`, color: tag.color, borderColor: tag.color }}
                                                        onClick={() => handleTagToggle(tag.id)}
                                                    >
                                                        {tag.name} ×
                                                    </Badge>
                                                ) : null;
                                            })}
                                        </div>
                                        <div className="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto">
                                            {tags
                                                .filter((tag) => !selectedTags.includes(tag.id))
                                                .map((tag) => (
                                                    <Badge
                                                        key={tag.id}
                                                        variant="outline"
                                                        className="cursor-pointer justify-start"
                                                        style={{ borderColor: tag.color }}
                                                        onClick={() => handleTagToggle(tag.id)}
                                                    >
                                                        <TagIcon className="mr-1 h-3 w-3" style={{ color: tag.color }} />
                                                        {tag.name}
                                                    </Badge>
                                                ))}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Featured Image */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Featured Image</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {selectedMedia ? (
                                        <div className="space-y-2">
                                            <img
                                                src={selectedMedia.path}
                                                alt={selectedMedia.alt_text || selectedMedia.name}
                                                className="h-32 w-full rounded-lg object-cover"
                                            />
                                            <p className="text-sm text-muted-foreground">{selectedMedia.name}</p>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => {
                                                    setSelectedMedia(null);
                                                    setData('featured_image_id', 'none');
                                                }}
                                            >
                                                Remove Image
                                            </Button>
                                        </div>
                                    ) : (
                                        <Button type="button" variant="outline" className="w-full" onClick={() => setShowMediaLibrary(true)}>
                                            <Image className="mr-2 h-4 w-4" />
                                            Select Featured Image
                                        </Button>
                                    )}

                                    {showMediaLibrary && (
                                        <div className="space-y-4 rounded-lg border p-4">
                                            <div className="flex items-center justify-between">
                                                <Label>Media Library</Label>
                                                <Button type="button" variant="ghost" size="sm" onClick={() => setShowMediaLibrary(false)}>
                                                    ×
                                                </Button>
                                            </div>
                                            <Input
                                                placeholder="Search media..."
                                                value={mediaSearch}
                                                onChange={(e) => setMediaSearch(e.target.value)}
                                            />
                                            <div className="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto">
                                                {filteredMedia.map((mediaItem) => (
                                                    <div
                                                        key={mediaItem.id}
                                                        className="cursor-pointer rounded-lg border p-2 hover:bg-accent"
                                                        onClick={() => handleMediaSelect(mediaItem)}
                                                    >
                                                        <img
                                                            src={mediaItem.path}
                                                            alt={mediaItem.alt_text || mediaItem.name}
                                                            className="h-16 w-full rounded object-cover"
                                                        />
                                                        <p className="mt-1 truncate text-xs text-muted-foreground">{mediaItem.name}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Actions */}
                            <Card>
                                <CardContent className="pt-6">
                                    <div className="flex gap-2">
                                        <Button type="submit" disabled={processing} className="flex-1">
                                            <Save className="mr-2 h-4 w-4" />
                                            {processing ? 'Creating...' : 'Create Post'}
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
