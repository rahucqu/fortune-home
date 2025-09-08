import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Eye } from 'lucide-react';

interface Post {
    id: number;
    title: string;
    slug: string;
    status: string;
    created_at: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    seo_title: string | null;
    seo_description: string | null;
    seo_keywords: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    posts: Post[];
}

interface ShowCategoryProps {
    category: Category;
}

export default function ShowCategory({ category }: ShowCategoryProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Categories', href: '/admin/categories' },
        { title: category.name, href: `/admin/categories/${category.id}` },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Category - ${category.name}`} />

            <div className="flex-1 space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">{category.name}</h2>
                        <p className="text-muted-foreground">Category details and associated posts.</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={`/admin/categories/${category.id}/edit`}>
                            <Button>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit Category
                            </Button>
                        </Link>
                        <Link href="/admin/categories">
                            <Button variant="outline">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Categories
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="font-medium">Name</h4>
                                    <p className="text-muted-foreground">{category.name}</p>
                                </div>
                                
                                <div>
                                    <h4 className="font-medium">Slug</h4>
                                    <p className="text-muted-foreground font-mono text-sm">{category.slug}</p>
                                </div>

                                {category.description && (
                                    <div>
                                        <h4 className="font-medium">Description</h4>
                                        <p className="text-muted-foreground">{category.description}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* SEO Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>SEO Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {category.seo_title ? (
                                    <div>
                                        <h4 className="font-medium">SEO Title</h4>
                                        <p className="text-muted-foreground">{category.seo_title}</p>
                                    </div>
                                ) : (
                                    <div>
                                        <h4 className="font-medium">SEO Title</h4>
                                        <p className="text-muted-foreground italic">Not set</p>
                                    </div>
                                )}

                                {category.seo_description ? (
                                    <div>
                                        <h4 className="font-medium">SEO Description</h4>
                                        <p className="text-muted-foreground">{category.seo_description}</p>
                                    </div>
                                ) : (
                                    <div>
                                        <h4 className="font-medium">SEO Description</h4>
                                        <p className="text-muted-foreground italic">Not set</p>
                                    </div>
                                )}

                                {category.seo_keywords ? (
                                    <div>
                                        <h4 className="font-medium">SEO Keywords</h4>
                                        <p className="text-muted-foreground">{category.seo_keywords}</p>
                                    </div>
                                ) : (
                                    <div>
                                        <h4 className="font-medium">SEO Keywords</h4>
                                        <p className="text-muted-foreground italic">Not set</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Associated Posts */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Posts</CardTitle>
                                <CardDescription>
                                    Posts in this category (showing latest 10)
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {category.posts && category.posts.length > 0 ? (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Title</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Created</TableHead>
                                                <TableHead className="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {category.posts.map((post) => (
                                                <TableRow key={post.id}>
                                                    <TableCell className="font-medium">
                                                        {post.title}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge
                                                            variant={post.status === 'published' ? 'default' : 'secondary'}
                                                        >
                                                            {post.status}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        {new Date(post.created_at).toLocaleDateString()}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Link href={`/admin/posts/${post.id}`}>
                                                            <Button variant="ghost" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-muted-foreground">No posts in this category yet.</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Category Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="font-medium">Status</h4>
                                    <Badge variant={category.is_active ? 'default' : 'secondary'}>
                                        {category.is_active ? 'Active' : 'Inactive'}
                                    </Badge>
                                </div>

                                <div>
                                    <h4 className="font-medium">Sort Order</h4>
                                    <p className="text-muted-foreground">{category.sort_order}</p>
                                </div>

                                <div>
                                    <h4 className="font-medium">Posts Count</h4>
                                    <p className="text-muted-foreground">
                                        {category.posts ? category.posts.length : 0} posts
                                    </p>
                                </div>

                                <div>
                                    <h4 className="font-medium">Created</h4>
                                    <p className="text-muted-foreground">
                                        {new Date(category.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
