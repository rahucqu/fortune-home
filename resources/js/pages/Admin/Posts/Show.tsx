import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Eye, MessageSquare, Star, Tag as TagIcon } from 'lucide-react';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    status: string;
    published_at: string | null;
    scheduled_at: string | null;
    is_featured: boolean;
    allow_comments: boolean;
    is_sticky: boolean;
    views_count: number;
    comments_count: number;
    sort_order: number;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
    };
    category: {
        id: number;
        name: string;
    } | null;
    featured_image: {
        id: number;
        name: string;
        path: string;
    } | null;
    tags: Array<{
        id: number;
        name: string;
        color: string;
    }>;
}

interface ShowProps {
    post: Post;
}

export default function Show({ post }: ShowProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Posts', href: '/admin/posts' },
        { title: post.title, href: `/admin/posts/${post.id}` },
    ];

    const getStatusBadge = (status: string) => {
        const variants = {
            published: 'default',
            draft: 'secondary',
            scheduled: 'outline',
            archived: 'destructive',
        } as const;

        return <Badge variant={variants[status as keyof typeof variants] || 'secondary'}>{status.charAt(0).toUpperCase() + status.slice(1)}</Badge>;
    };

    const formatDate = (dateString: string | null) => {
        if (!dateString) return 'Not set';
        return new Date(dateString).toLocaleString();
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Post: ${post.title}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('admin.posts.index')}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Posts
                        </Link>
                    </Button>
                    <div className="flex-1">
                        <div className="flex items-center gap-2">
                            <h1 className="text-3xl font-bold tracking-tight">{post.title}</h1>
                            {post.is_featured && <Star className="h-6 w-6 fill-current text-yellow-500" />}
                        </div>
                        <div className="mt-2 flex items-center gap-4 text-muted-foreground">
                            <span>By {post.user.name}</span>
                            <span>•</span>
                            <span>{formatDate(post.created_at)}</span>
                            <span>•</span>
                            {getStatusBadge(post.status)}
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.posts.edit', post.id)}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Post
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="space-y-6 lg:col-span-2">
                        {/* Featured Image */}
                        {post.featured_image && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Featured Image</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={post.featured_image.path}
                                        alt={post.featured_image.name}
                                        className="h-64 w-full rounded-lg object-cover"
                                    />
                                    <p className="mt-2 text-sm text-muted-foreground">{post.featured_image.name}</p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Excerpt */}
                        {post.excerpt && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Excerpt</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground italic">{post.excerpt}</p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Content */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Content</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="prose prose-sm dark:prose-invert max-w-none" dangerouslySetInnerHTML={{ __html: post.content }} />
                            </CardContent>
                        </Card>

                        {/* SEO Information */}
                        {(post.meta_title || post.meta_description || post.meta_keywords) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Information</CardTitle>
                                    <CardDescription>Search engine optimization details</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {post.meta_title && (
                                        <div>
                                            <h4 className="mb-1 font-medium">Meta Title</h4>
                                            <p className="text-sm text-muted-foreground">{post.meta_title}</p>
                                        </div>
                                    )}
                                    {post.meta_description && (
                                        <div>
                                            <h4 className="mb-1 font-medium">Meta Description</h4>
                                            <p className="text-sm text-muted-foreground">{post.meta_description}</p>
                                        </div>
                                    )}
                                    {post.meta_keywords && (
                                        <div>
                                            <h4 className="mb-1 font-medium">Meta Keywords</h4>
                                            <p className="text-sm text-muted-foreground">{post.meta_keywords}</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Post Stats */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Post Statistics</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Eye className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">Views</span>
                                    </div>
                                    <span className="font-medium">{post.views_count}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <MessageSquare className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-sm">Comments</span>
                                    </div>
                                    <span className="font-medium">{post.comments_count}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Publishing Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Publishing Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="mb-1 font-medium">Status</h4>
                                    {getStatusBadge(post.status)}
                                </div>

                                <Separator />

                                <div>
                                    <h4 className="mb-1 font-medium">Author</h4>
                                    <p className="text-sm text-muted-foreground">{post.user.name}</p>
                                </div>

                                <div>
                                    <h4 className="mb-1 font-medium">Created</h4>
                                    <p className="text-sm text-muted-foreground">{formatDate(post.created_at)}</p>
                                </div>

                                <div>
                                    <h4 className="mb-1 font-medium">Last Updated</h4>
                                    <p className="text-sm text-muted-foreground">{formatDate(post.updated_at)}</p>
                                </div>

                                {post.published_at && (
                                    <div>
                                        <h4 className="mb-1 font-medium">Published</h4>
                                        <p className="text-sm text-muted-foreground">{formatDate(post.published_at)}</p>
                                    </div>
                                )}

                                {post.scheduled_at && (
                                    <div>
                                        <h4 className="mb-1 font-medium">Scheduled</h4>
                                        <p className="text-sm text-muted-foreground">{formatDate(post.scheduled_at)}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Category */}
                        {post.category && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Category</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Badge variant="outline">{post.category.name}</Badge>
                                </CardContent>
                            </Card>
                        )}

                        {/* Tags */}
                        {post.tags.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tags</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {post.tags.map((tag) => (
                                            <Badge
                                                key={tag.id}
                                                variant="secondary"
                                                style={{ backgroundColor: `${tag.color}20`, color: tag.color, borderColor: tag.color }}
                                            >
                                                <TagIcon className="mr-1 h-3 w-3" />
                                                {tag.name}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Post Settings */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Post Settings</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Featured Post</span>
                                    <Badge variant={post.is_featured ? 'default' : 'secondary'}>{post.is_featured ? 'Yes' : 'No'}</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Allow Comments</span>
                                    <Badge variant={post.allow_comments ? 'default' : 'secondary'}>{post.allow_comments ? 'Yes' : 'No'}</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Sticky Post</span>
                                    <Badge variant={post.is_sticky ? 'default' : 'secondary'}>{post.is_sticky ? 'Yes' : 'No'}</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Sort Order</span>
                                    <span className="text-sm font-medium">{post.sort_order}</span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Post URL */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Post URL</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <Label className="text-sm font-medium">Slug</Label>
                                    <p className="rounded bg-muted p-2 font-mono text-sm text-muted-foreground">{post.slug}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
