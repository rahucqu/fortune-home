import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface CategoryFormData {
    name: string;
    slug: string;
    description: string;
    seo_title: string;
    seo_description: string;
    seo_keywords: string;
    is_active: boolean;
    sort_order: number;
}

export default function CreateCategory() {
    const { data, setData, post, processing, errors } = useForm<CategoryFormData>({
        name: '',
        slug: '',
        description: '',
        seo_title: '',
        seo_description: '',
        seo_keywords: '',
        is_active: true,
        sort_order: 0,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Categories', href: '/admin/categories' },
        { title: 'Create', href: '/admin/categories/create' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/categories');
    };

    const generateSlug = (name: string) => {
        return name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const name = e.target.value;
        setData('name', name);
        
        // Auto-generate slug if slug field is empty
        if (!data.slug) {
            setData('slug', generateSlug(name));
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Category" />

            <div className="flex-1 space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Create Category</h2>
                        <p className="text-muted-foreground">Add a new blog category.</p>
                    </div>
                    <Link href="/admin/categories">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Categories
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Content */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Basic Information</CardTitle>
                                    <CardDescription>
                                        Basic category details and content.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="name">Name *</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={handleNameChange}
                                            placeholder="Enter category name"
                                            className={errors.name ? 'border-destructive' : ''}
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-destructive mt-1">{errors.name}</p>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="slug">Slug</Label>
                                        <Input
                                            id="slug"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            placeholder="category-slug (auto-generated)"
                                            className={errors.slug ? 'border-destructive' : ''}
                                        />
                                        {errors.slug && (
                                            <p className="text-sm text-destructive mt-1">{errors.slug}</p>
                                        )}
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Leave empty to auto-generate from name
                                        </p>
                                    </div>

                                    <div>
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                                            placeholder="Enter category description"
                                            rows={4}
                                            className={errors.description ? 'border-destructive' : ''}
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-destructive mt-1">{errors.description}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* SEO Section */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Settings</CardTitle>
                                    <CardDescription>
                                        Search engine optimization settings for this category.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="seo_title">SEO Title</Label>
                                        <Input
                                            id="seo_title"
                                            value={data.seo_title}
                                            onChange={(e) => setData('seo_title', e.target.value)}
                                            placeholder="SEO optimized title"
                                            className={errors.seo_title ? 'border-destructive' : ''}
                                        />
                                        {errors.seo_title && (
                                            <p className="text-sm text-destructive mt-1">{errors.seo_title}</p>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="seo_description">SEO Description</Label>
                                        <Textarea
                                            id="seo_description"
                                            value={data.seo_description}
                                            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('seo_description', e.target.value)}
                                            placeholder="SEO meta description (max 160 characters)"
                                            rows={3}
                                            maxLength={160}
                                            className={errors.seo_description ? 'border-destructive' : ''}
                                        />
                                        {errors.seo_description && (
                                            <p className="text-sm text-destructive mt-1">{errors.seo_description}</p>
                                        )}
                                        <p className="text-sm text-muted-foreground mt-1">
                                            {data.seo_description.length}/160 characters
                                        </p>
                                    </div>

                                    <div>
                                        <Label htmlFor="seo_keywords">SEO Keywords</Label>
                                        <Input
                                            id="seo_keywords"
                                            value={data.seo_keywords}
                                            onChange={(e) => setData('seo_keywords', e.target.value)}
                                            placeholder="keyword1, keyword2, keyword3"
                                            className={errors.seo_keywords ? 'border-destructive' : ''}
                                        />
                                        {errors.seo_keywords && (
                                            <p className="text-sm text-destructive mt-1">{errors.seo_keywords}</p>
                                        )}
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Separate keywords with commas
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Category Settings</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div className="space-y-0.5">
                                            <Label>Active Status</Label>
                                            <p className="text-sm text-muted-foreground">
                                                Whether this category is active
                                            </p>
                                        </div>
                                        <Switch
                                            checked={data.is_active}
                                            onCheckedChange={(checked: boolean) => setData('is_active', checked)}
                                        />
                                    </div>

                                    <div>
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                            placeholder="0"
                                            min="0"
                                            className={errors.sort_order ? 'border-destructive' : ''}
                                        />
                                        {errors.sort_order && (
                                            <p className="text-sm text-destructive mt-1">{errors.sort_order}</p>
                                        )}
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Lower numbers appear first
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Actions */}
                            <Card>
                                <CardContent className="pt-6">
                                    <div className="flex flex-col gap-2">
                                        <Button type="submit" disabled={processing} className="w-full">
                                            {processing ? 'Creating...' : 'Create Category'}
                                        </Button>
                                        <Link href="/admin/categories">
                                            <Button variant="outline" className="w-full">
                                                Cancel
                                            </Button>
                                        </Link>
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
