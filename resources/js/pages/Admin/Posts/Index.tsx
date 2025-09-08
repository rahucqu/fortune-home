import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Calendar, Copy, Edit, Eye, Eye as EyeIcon, FileText, MoreHorizontal, Plus, Search, Star, Trash2, Users } from 'lucide-react';
import { useState } from 'react';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    status: 'draft' | 'published' | 'scheduled' | 'archived';
    is_featured: boolean;
    views_count: number;
    comments_count: number;
    published_at: string | null;
    scheduled_at: string | null;
    created_at: string;
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

interface Category {
    id: number;
    name: string;
}

interface Author {
    id: number;
    name: string;
}

interface PostsIndexProps {
    posts: {
        data: Post[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total: number;
        published: number;
        draft: number;
        featured: number;
    };
    filters: {
        search: string;
        status: string;
        category: string;
        author: string;
    };
    categories: Category[];
    authors: Author[];
}

export default function PostsIndex({ posts, stats, filters, categories, authors }: PostsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');
    const [category, setCategory] = useState(filters.category || 'all');
    const [author, setAuthor] = useState(filters.author || 'all');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Posts', href: '/admin/posts' },
    ];

    const handleSearch = () => {
        router.get(
            route('admin.posts.index'),
            {
                search,
                status: status === 'all' ? '' : status,
                category: category === 'all' ? '' : category,
                author: author === 'all' ? '' : author,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        setCategory('all');
        setAuthor('all');
        router.get(
            route('admin.posts.index'),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleDelete = (postId: number) => {
        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(route('admin.posts.destroy', postId));
        }
    };

    const handlePublish = (postId: number) => {
        router.patch(route('admin.posts.publish', postId));
    };

    const handleUnpublish = (postId: number) => {
        router.patch(route('admin.posts.unpublish', postId));
    };

    const handleToggleFeatured = (postId: number) => {
        router.patch(route('admin.posts.toggle-featured', postId));
    };

    const handleDuplicate = (postId: number) => {
        router.post(route('admin.posts.duplicate', postId));
    };

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
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString();
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Posts" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Posts</h1>
                        <p className="text-muted-foreground">Manage your blog posts</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.posts.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            New Post
                        </Link>
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center space-x-2">
                                <FileText className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Total Posts</p>
                                    <p className="text-2xl font-bold">{stats.total}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center space-x-2">
                                <EyeIcon className="h-4 w-4 text-green-600" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Published</p>
                                    <p className="text-2xl font-bold text-green-600">{stats.published}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center space-x-2">
                                <Edit className="h-4 w-4 text-yellow-600" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Drafts</p>
                                    <p className="text-2xl font-bold text-yellow-600">{stats.draft}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center space-x-2">
                                <Star className="h-4 w-4 text-blue-600" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Featured</p>
                                    <p className="text-2xl font-bold text-blue-600">{stats.featured}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filter Posts</CardTitle>
                        <CardDescription>Use the filters below to find specific posts</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-5">
                            <div className="space-y-2">
                                <Input
                                    placeholder="Search posts..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                />
                            </div>
                            <div className="space-y-2">
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="published">Published</SelectItem>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="scheduled">Scheduled</SelectItem>
                                        <SelectItem value="archived">Archived</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Select value={category} onValueChange={setCategory}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Categories</SelectItem>
                                        {categories.map((cat) => (
                                            <SelectItem key={cat.id} value={cat.id.toString()}>
                                                {cat.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Select value={author} onValueChange={setAuthor}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Authors" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Authors</SelectItem>
                                        {authors.map((auth) => (
                                            <SelectItem key={auth.id} value={auth.id.toString()}>
                                                {auth.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex gap-2">
                                <Button onClick={handleSearch} size="sm">
                                    <Search className="mr-2 h-4 w-4" />
                                    Search
                                </Button>
                                <Button variant="outline" onClick={clearFilters} size="sm">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Posts Table */}
                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Author</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Tags</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Views</TableHead>
                                    <TableHead>Comments</TableHead>
                                    <TableHead>Published</TableHead>
                                    <TableHead className="w-[50px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {posts.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="py-8 text-center">
                                            <div className="text-muted-foreground">
                                                No posts found.{' '}
                                                <Link href={route('admin.posts.create')} className="text-primary hover:underline">
                                                    Create your first post
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    posts.data.map((post) => (
                                        <TableRow key={post.id}>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-2">
                                                        <Link href={route('admin.posts.show', post.id)} className="font-medium hover:underline">
                                                            {post.title}
                                                        </Link>
                                                        {post.is_featured && <Star className="h-4 w-4 fill-current text-yellow-500" />}
                                                    </div>
                                                    {post.excerpt && <p className="line-clamp-1 text-sm text-muted-foreground">{post.excerpt}</p>}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">{post.user.name}</div>
                                            </TableCell>
                                            <TableCell>
                                                {post.category ? (
                                                    <Badge variant="outline">{post.category.name}</Badge>
                                                ) : (
                                                    <span className="text-sm text-muted-foreground">No category</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1">
                                                    {post.tags.slice(0, 2).map((tag) => (
                                                        <Badge
                                                            key={tag.id}
                                                            variant="secondary"
                                                            className="text-xs"
                                                            style={{ backgroundColor: `${tag.color}20`, color: tag.color, borderColor: tag.color }}
                                                        >
                                                            {tag.name}
                                                        </Badge>
                                                    ))}
                                                    {post.tags.length > 2 && (
                                                        <Badge variant="secondary" className="text-xs">
                                                            +{post.tags.length - 2}
                                                        </Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>{getStatusBadge(post.status)}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1">
                                                    <EyeIcon className="h-3 w-3 text-muted-foreground" />
                                                    <span className="text-sm">{post.views_count}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1">
                                                    <Users className="h-3 w-3 text-muted-foreground" />
                                                    <span className="text-sm">{post.comments_count}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    {post.status === 'scheduled' ? formatDate(post.scheduled_at) : formatDate(post.published_at)}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" className="h-8 w-8 p-0">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('admin.posts.show', post.id)}>
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('admin.posts.edit', post.id)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onClick={() => handleDuplicate(post.id)}>
                                                            <Copy className="mr-2 h-4 w-4" />
                                                            Duplicate
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onClick={() => handleToggleFeatured(post.id)}>
                                                            <Star className="mr-2 h-4 w-4" />
                                                            {post.is_featured ? 'Remove Featured' : 'Mark Featured'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        {post.status === 'published' ? (
                                                            <DropdownMenuItem onClick={() => handleUnpublish(post.id)}>
                                                                <Calendar className="mr-2 h-4 w-4" />
                                                                Unpublish
                                                            </DropdownMenuItem>
                                                        ) : (
                                                            <DropdownMenuItem onClick={() => handlePublish(post.id)}>
                                                                <Calendar className="mr-2 h-4 w-4" />
                                                                Publish
                                                            </DropdownMenuItem>
                                                        )}
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => handleDelete(post.id)} className="text-destructive">
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {posts.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-muted-foreground">
                            Showing {(posts.current_page - 1) * posts.per_page + 1} to {Math.min(posts.current_page * posts.per_page, posts.total)} of{' '}
                            {posts.total} results
                        </div>
                        <div className="flex items-center space-x-2">
                            {posts.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => link.url && router.get(link.url)}
                                    disabled={!link.url}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
